<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\DetectedPivot;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class RelationBuilder
{
    private RelationCollection $relationCollection;

    /** @var Collection<int, string>|null */
    private ?Collection $allTables = null;

    public function __construct(
        private Schema $schema,
        private BelongsToManyBuilder $belongsToManyRelationBuilder,
        private BelongsToBuilder $belongsToBuilder,
        private HasManyBuilder $hasManyBuilder,
    ) {
    }

    public function build(string $tableName, ?string $belongsToManyTableList): RelationCollection
    {
        $this->relationCollection = new RelationCollection();

        $this->buildBelongsToMany($tableName, $belongsToManyTableList);
        $this->buildBelongsTo($tableName);
        $this->buildHasMany($tableName);

        return $this->relationCollection;
    }

    /**
     * @return Collection<int, string>
     */
    private function allTables(): Collection
    {
        return $this->allTables ??= (new Collection($this->schema->getTables()))
            ->pluck('name');
    }

    private function buildBelongsToMany(string $tableName, ?string $belongsToManyTableList): void
    {
        if ($belongsToManyTableList === null) {
            $this->detectBelongsToManyForTable($tableName);

            return;
        }

        $this->buildBelongsToManyFromString($tableName, $belongsToManyTableList);
    }

    private function buildBelongsTo(string $tableName): void
    {
        $this->detectBelongsTo($tableName);
    }

    private function buildBelongsToManyFromString(string $tableName, ?string $belongsToManyTableList): void
    {
        (new Collection(explode(',', $belongsToManyTableList)))
            ->filter(fn (string $relatedTable): bool => $this->schema->hasTable($relatedTable))
            ->each(function (string $relatedTable) use ($tableName): void {
                $this->relationCollection->pushBelongsToMany(
                    $this->belongsToManyRelationBuilder->build($relatedTable, $tableName),
                );
            });
    }

    private function detectBelongsToManyForTable(string $tableName): void
    {
        $this->allTables()->each(function (string $candidateTable) use ($tableName): void {
            $pivot = $this->detectPivotRelation($candidateTable, $tableName);
            if ($pivot === null) {
                return;
            }

            $this->relationCollection->pushBelongsToMany(
                $this->belongsToManyRelationBuilder->build(
                    $pivot->relatedTable,
                    $tableName,
                    $pivot,
                ),
            );
        });
    }

    private function detectBelongsTo(string $tableName): void
    {
        $columns = new Collection($this->schema->getColumns($tableName));

        $columns->filter(
            static fn (array $column): bool => str_ends_with($column['name'], '_id')
                && !in_array(
                    $column['name'],
                    ['created_by_admin_user_id', 'updated_by_admin_user_id', 'current_team_id'],
                    true,
                ),
        )->each(function (array $column): void {
            $relatedTable = Str::plural(Str::beforeLast($column['name'], '_id'));

            if (!$this->allTables()->contains($relatedTable)) {
                return;
            }

            $this->relationCollection->pushBelongsTo(
                $this->belongsToBuilder->build($column['name'], $relatedTable),
            );
        });
    }

    private function buildHasMany(string $tableName): void
    {
        $this->detectHasMany($tableName);
    }

    private function detectHasMany(string $tableName): void
    {
        $expectedForeignKey = sprintf('%s_id', Str::singular($tableName));

        $this->allTables()->each(function (string $candidateTable) use ($tableName, $expectedForeignKey): void {
            if ($candidateTable === $tableName) {
                return;
            }

            if ($this->relationCollection->isPivotTable($candidateTable)) {
                return;
            }

            $columns = new Collection($this->schema->getColumns($candidateTable));

            $hasForeignKey = $columns->contains(
                static fn (array $column): bool => $column['name'] === $expectedForeignKey,
            );

            if (!$hasForeignKey) {
                return;
            }

            $this->relationCollection->pushHasMany(
                $this->hasManyBuilder->build($expectedForeignKey, $candidateTable),
            );
        });
    }

    private function detectPivotRelation(string $candidateTable, string $tableName): ?DetectedPivot
    {
        $pivot = $this->detectPivotViaForeignKeys($candidateTable, $tableName);
        if ($pivot !== null) {
            return $pivot;
        }

        return $this->detectPivotViaNamingConvention($candidateTable, $tableName);
    }

    private function detectPivotViaForeignKeys(string $candidateTable, string $tableName): ?DetectedPivot
    {
        $foreignKeys = new Collection($this->schema->getForeignKeys($candidateTable));

        if ($foreignKeys->count() !== 2) {
            return null;
        }

        if (!$this->looksLikePivotTable($candidateTable, $tableName)) {
            return null;
        }

        $ownForeignKey = $foreignKeys->first(
            static fn (array $foreignKey): bool => $foreignKey['foreign_table'] === $tableName,
        );
        $relatedForeignKey = $foreignKeys->first(
            static fn (array $foreignKey): bool => $foreignKey['foreign_table'] !== $tableName,
        );

        if ($ownForeignKey === null || $relatedForeignKey === null) {
            return null;
        }

        return new DetectedPivot(
            pivotTable: $candidateTable,
            relatedTable: $relatedForeignKey['foreign_table'],
            foreignKey: $ownForeignKey['columns'][0],
            relatedKey: $relatedForeignKey['columns'][0],
        );
    }

    private function detectPivotViaNamingConvention(string $candidateTable, string $tableName): ?DetectedPivot
    {
        if (!$this->looksLikePivotTable($candidateTable, $tableName)) {
            return null;
        }

        $currentTableFk = sprintf('%s_id', Str::singular($tableName));
        $otherFk = (new Collection($this->schema->getColumns($candidateTable)))
            ->filter(static fn (array $column): bool => str_ends_with($column['name'], '_id'))
            ->first(static fn (array $column): bool => $column['name'] !== $currentTableFk);

        if ($otherFk === null) {
            return null;
        }

        $relatedTable = Str::plural(Str::beforeLast($otherFk['name'], '_id'));

        if (!$this->allTables()->contains($relatedTable)) {
            return null;
        }

        return new DetectedPivot(
            pivotTable: $candidateTable,
            relatedTable: $relatedTable,
            foreignKey: $currentTableFk,
            relatedKey: $otherFk['name'],
        );
    }

    /**
     * A pivot table carries nothing but its two foreign keys, plus an optional surrogate key and
     * timestamps. Any further column -- a uuid, a type, a title, soft deletes -- means this is an
     * entity that merely happens to reference two tables, not a pivot: `alerts`, for instance,
     * references both `users` (created_by_user_id) and `monitors`, and would otherwise be read as
     * a monitors/users pivot.
     */
    private function looksLikePivotTable(string $candidateTable, string $tableName): bool
    {
        $columns = new Collection($this->schema->getColumns($candidateTable));

        $idColumns = $columns->filter(
            static fn (array $column): bool => str_ends_with($column['name'], '_id'),
        );

        if ($idColumns->count() !== 2) {
            return false;
        }

        $hasPayloadColumn = $columns->contains(
            static fn (array $column): bool => !str_ends_with($column['name'], '_id')
                && !in_array($column['name'], ['id', 'created_at', 'updated_at'], true),
        );

        if ($hasPayloadColumn) {
            return false;
        }

        $currentTableFk = sprintf('%s_id', Str::singular($tableName));

        return $idColumns->contains(static fn (array $column): bool => $column['name'] === $currentTableFk);
    }
}
