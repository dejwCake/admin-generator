<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\BelongsTo;
use Brackets\AdminGenerator\Naming;
use Illuminate\Support\Str;

final readonly class BelongsToBuilder
{
    public function __construct(private ColumnCollectionBuilder $columnCollectionBuilder)
    {
    }

    public function build(string $foreignKeyColumn, string $relatedTable): BelongsTo
    {
        $relatedModelName = Naming::modelName($relatedTable);

        return new BelongsTo(
            foreignKeyColumn: $foreignKeyColumn,
            relatedTable: $relatedTable,
            relatedModel: sprintf('App\\Models\\%s', $relatedModelName),
            relatedModelName: $relatedModelName,
            relatedLabel: $this->columnCollectionBuilder->build($relatedTable)->getLabelColumn(),
            relationMethodName: Str::camel(Str::beforeLast($foreignKeyColumn, '_id')),
            optionsAttributeName: sprintf('%s-options', Str::singular(str_replace('_', '-', $relatedTable))),
            optionsPropName: sprintf('%sOptions', Str::camel(Str::singular($relatedTable))),
        );
    }
}
