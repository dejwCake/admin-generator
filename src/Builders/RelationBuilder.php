<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class RelationBuilder
{
    private RelationCollection $relationCollection;

    /** @var Collection<int, string> */
    private Collection $allTables;

    public function __construct(
        private Schema $schema,
        private BelongsToManyBuilder $belongsToManyRelationBuilder,
        private BelongsToBuilder $belongsToBuilder,
        private HasManyBuilder $hasManyBuilder,
    ) {
        $this->allTables = (new Collection($this->schema->getTables()))
            ->pluck('name');
    }

    public function build(string $tableName, ?string $belongsToManyTableList): RelationCollection
    {
        $this->relationCollection = new RelationCollection();

        $this->buildBelongsToMany($tableName, $belongsToManyTableList);
        $this->buildBelongsTo($tableName);
        $this->buildHasMany($tableName);

        return $this->relationCollection;
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
        $this->allTables->each(function (string $candidateTable) use ($tableName): void {
            $relatedTable = $this->detectPivotRelation($candidateTable, $tableName);
            if ($relatedTable === null) {
                return;
            }

            $this->relationCollection->pushBelongsToMany(
                $this->belongsToManyRelationBuilder->build($relatedTable, $tableName),
            );
        });
    }

    private function detectBelongsTo(string $tableName): void
    {
        $columns = new Collection($this->schema->getColumns($tableName));

        $columns->filter(
            static fn (array $column): bool => str_ends_with($column['name'], '_id')
                && !in_array($column['name'], ['created_by_admin_user_id', 'updated_by_admin_user_id'], true),
        )->each(function (array $column): void {
            $relatedTable = Str::plural(Str::beforeLast($column['name'], '_id'));

            if (!$this->allTables->contains($relatedTable)) {
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
        $expectedForeignKey = Str::singular($tableName) . '_id';

        $this->allTables->each(function (string $candidateTable) use ($tableName, $expectedForeignKey): void {
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

    private function detectPivotRelation(string $candidateTable, string $tableName): ?string
    {
        $relatedTable = $this->detectPivotViaForeignKeys($candidateTable, $tableName);
        if ($relatedTable !== null) {
            return $relatedTable;
        }

        return $this->detectPivotViaNamingConvention($candidateTable, $tableName);
    }

    private function detectPivotViaForeignKeys(string $candidateTable, string $tableName): ?string
    {
        $foreignKeys = $this->schema->getForeignKeys($candidateTable);

        if (count($foreignKeys) !== 2) {
            return null;
        }

        $referencedTables = array_map(
            static fn (array $foreignKey): string => $foreignKey['foreign_table'],
            $foreignKeys,
        );


        if (!in_array($tableName, $referencedTables, true)) {
            return null;
        }

        foreach ($referencedTables as $table) {
            if ($table !== $tableName) {
                return $table;
            }
        }

        return null;
    }

    private function detectPivotViaNamingConvention(string $candidateTable, string $tableName): ?string
    {
        $columns = new Collection($this->schema->getColumns($candidateTable));
        $idColumns = $columns->filter(
            static fn (array $column): bool => str_ends_with($column['name'], '_id'),
        );

        if ($idColumns->count() !== 2) {
            return null;
        }

        $otherColumns = $columns->reject(
            static fn (array $column): bool => str_ends_with($column['name'], '_id')
                || in_array($column['name'], ['id', 'created_at', 'updated_at'], true),
        );
        if ($otherColumns->isNotEmpty()) {
            return null;
        }

        $currentTableFk = Str::singular($tableName) . '_id';
        if (!$idColumns->contains(static fn (array $column): bool => $column['name'] === $currentTableFk)) {
            return null;
        }

        $otherFk = $idColumns->first(static fn (array $column): bool => $column['name'] !== $currentTableFk);
        $relatedTable = Str::plural(Str::beforeLast($otherFk['name'], '_id'));

        if (!$this->allTables->contains($relatedTable)) {
            return null;
        }

        return $relatedTable;
    }
}
