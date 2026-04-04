<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class BelongsToManyBuilder
{
    public function __construct(private ColumnCollectionBuilder $columnCollectionBuilder,)
    {
    }

    public function build(string $relatedTable, string $tableName,): BelongsToMany
    {
        return new BelongsToMany(
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
