<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class RelationBuilder
{
    public function __construct(private Schema $schema, private BelongsToManyBuilder $belongsToManyRelationBuilder,)
    {
    }

    public function build(string $tableName, ?string $belongsToManyTableList): RelationCollection
    {
        if ($belongsToManyTableList === null) {
            return $this->detectForTable($tableName);
        }

        return $this->buildFromString($tableName, $belongsToManyTableList);
    }

    private function buildFromString(string $tableName, ?string $belongsToManyTableList): RelationCollection
    {
        $relationCollection = new RelationCollection();

        (new Collection(explode(',', $belongsToManyTableList)))
            ->filter(fn (string $relatedTable): bool => $this->schema->hasTable($relatedTable))
            ->each(function (string $relatedTable) use ($relationCollection, $tableName): void {
                $relationCollection->pushBelongsToMany(
                    $this->belongsToManyRelationBuilder->build($relatedTable, $tableName),
                );
            });

        return $relationCollection;
    }

    private function detectForTable(string $tableName): RelationCollection
    {
        $relationCollection = new RelationCollection();
        $allTables = $this->schema->getTableListing();

        foreach ($allTables as $candidateTable) {
            $relatedTable = $this->detectPivotRelation($candidateTable, $tableName, $allTables);
            if ($relatedTable === null) {
                continue;
            }

            $relationCollection->pushBelongsToMany(
                $this->belongsToManyRelationBuilder->build($relatedTable, $tableName),
            );
        }

        return $relationCollection;
    }

    private function detectPivotRelation(string $candidateTable, string $tableName, array $allTables): ?string
    {
        $relatedTable = $this->detectViaForeignKeys($candidateTable, $tableName);
        if ($relatedTable !== null) {
            return $relatedTable;
        }

        return $this->detectViaNamingConvention($candidateTable, $tableName, $allTables);
    }

    private function detectViaForeignKeys(string $candidateTable, string $tableName): ?string
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

    private function detectViaNamingConvention(string $candidateTable, string $tableName, array $allTables): ?string
    {
        $columns = new Collection($this->schema->getColumns($candidateTable));
        $idColumns = $columns->filter(
            static fn (array $col): bool => str_ends_with($col['name'], '_id'),
        );

        if ($idColumns->count() !== 2) {
            return null;
        }

        $otherColumns = $columns->reject(
            static fn (array $col): bool => str_ends_with($col['name'], '_id')
                || in_array($col['name'], ['id', 'created_at', 'updated_at'], true),
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

        if (!in_array($relatedTable, $allTables, true)) {
            return null;
        }

        return $relatedTable;
    }
}
