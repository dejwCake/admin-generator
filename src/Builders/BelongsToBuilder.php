<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\BelongsTo;
use Illuminate\Support\Str;

final readonly class BelongsToBuilder
{
    public function __construct(private ColumnCollectionBuilder $columnCollectionBuilder)
    {
    }

    public function build(string $foreignKeyColumn, string $relatedTable): BelongsTo
    {
        $relatedModelName = Str::studly(Str::singular($relatedTable));

        return new BelongsTo(
            foreignKeyColumn: $foreignKeyColumn,
            relatedTable: $relatedTable,
            relatedModel: 'App\\Models\\' . $relatedModelName,
            relatedModelName: $relatedModelName,
            optionsPropName: Str::camel(Str::singular($relatedTable)) . 'Options',
            foreignKeyLabel: $this->columnCollectionBuilder->build($relatedTable)->getLabelColumn(),
            relationMethodName: Str::camel(Str::beforeLast($foreignKeyColumn, '_id')),
        );
    }
}
