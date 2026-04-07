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
                : sprintf('App\\Models\\%s', Naming::modelName($relatedTable)),
            relatedModelName: Naming::modelName($relatedTable),
            relatedLabel: $this->columnCollectionBuilder->build($relatedTable)->getLabelColumn(),
            relationTable: trim($this->getRelationTable($tableName, $relatedTable), '_'),
            relationMethodName: Str::camel($relatedTable),
            relationTranslationKey: Str::lcfirst(Str::plural(Naming::modelName($relatedTable))),
            relationTranslationValue: Str::headline($relatedTable),
            optionsAttributeName: sprintf('%s-options', Str::singular(str_replace('_', '-', $relatedTable))),
            optionsPropName: sprintf('%sOptions', Str::camel(Str::singular($relatedTable))),
            foreignKey: sprintf('%s_id', Str::singular($tableName)),
            relatedKey: sprintf('%s_id', Str::singular($relatedTable)),
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
