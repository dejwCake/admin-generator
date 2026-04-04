<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\BelongsToManyRelation;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class BelongsToManyRelationBuilder
{
    public function __construct(private Schema $schema, private ColumnCollectionBuilder $columnCollectionBuilder,)
    {
    }

    public function build(string $belongsToManyTables, string $tableName): RelationCollection
    {
        $relationCollection = new RelationCollection();

        (new Collection(explode(',', $belongsToManyTables)))
            ->filter(fn (string $relatedTable): bool => $this->schema->hasTable($relatedTable))
            ->each(function (string $relatedTable) use ($relationCollection, $tableName): void {
                $relationCollection->pushBelongsToMany(
                    $this->buildRelation($relatedTable, $tableName),
                );
            });

        return $relationCollection;
    }

    public function detectForTable(string $tableName): RelationCollection
    {
        $relationCollection = new RelationCollection();
        $allTables = $this->schema->getTableListing();

        foreach ($allTables as $candidateTable) {
            $relatedTable = $this->detectPivotRelation($candidateTable, $tableName, $allTables);
            if ($relatedTable === null) {
                continue;
            }

            $relationCollection->pushBelongsToMany(
                $this->buildRelation($relatedTable, $tableName),
            );
        }

        return $relationCollection;
    }

    private function buildRelation(string $relatedTable, string $tableName,): BelongsToManyRelation
    {
        return new BelongsToManyRelation(
            currentTable: $tableName,
            relatedTable: $relatedTable,
            relatedModel: $relatedTable === 'roles'
                ? 'Spatie\\Permission\\Models\\Role'
                : 'App\\Models\\' . Str::studly(Str::singular($relatedTable)),
            relatedModelName: Str::studly(Str::singular($relatedTable)),
            relatedModelNamePlural: Str::studly($relatedTable),
            relatedModelVariableName: lcfirst(Str::singular(class_basename($relatedTable))),
            relationTable: trim($this->getRelationTable($tableName, $relatedTable), '_'),
            foreignKey: Str::singular($tableName) . '_id',
            relatedKey: Str::singular($relatedTable) . '_id',
            relatedLabel: $this->columnCollectionBuilder->build($relatedTable)->getLabelColumn(),
        );
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
            static fn (array $fk): string => $fk['foreign_table'],
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
        if (!$idColumns->contains(static fn (array $col): bool => $col['name'] === $currentTableFk)) {
            return null;
        }

        $otherFk = $idColumns->first(static fn (array $col): bool => $col['name'] !== $currentTableFk);
        $relatedTable = Str::plural(Str::beforeLast($otherFk['name'], '_id'));

        if (!in_array($relatedTable, $allTables, true)) {
            return null;
        }

        return $relatedTable;
    }

    private function getRelationTable(string $tableName, string $relatedTable): string
    {
        return (string) (new Collection([$tableName, $relatedTable]))
            ->sortBy(static fn (string $table): string => $table)
            ->reduce(
                static fn (string $relationTable, string $table): string => Str::singular(
                    $relationTable,
                ) . '_' . Str::singular(
                    $table,
                ),
                '',
            );
    }
}
