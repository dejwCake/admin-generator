<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\BelongsToMany;
use Brackets\AdminGenerator\Naming;
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
            relatedTable: $relatedTable,
            relatedModel: $relatedTable === 'roles'
                ? 'Spatie\\Permission\\Models\\Role'
                : 'App\\Models\\' . Naming::modelName($relatedTable),
            relatedModelName: Naming::modelName($relatedTable),
            relatedLabel: $this->columnCollectionBuilder->build($relatedTable)->getLabelColumn(),
            relationTable: trim($this->getRelationTable($tableName, $relatedTable), '_'),
            relationMethodName: Str::camel($relatedTable),
            relationTranslationKey: Str::lcfirst(Str::plural(Naming::modelName($relatedTable))),
            relationTranslationValue: Str::headline($relatedTable),
            optionsAttributeName: Str::singular(str_replace('_', '-', $relatedTable)) . '-options',
            optionsPropName: Str::camel(Str::singular($relatedTable)) . 'Options',
            foreignKey: Str::singular($tableName) . '_id',
            relatedKey: Str::singular($relatedTable) . '_id',
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
