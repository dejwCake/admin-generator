<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Relations\HasMany;
use Brackets\AdminGenerator\Naming;
use Illuminate\Support\Str;

final readonly class HasManyBuilder
{
    public function build(string $foreignKeyColumn, string $relatedTable): HasMany
    {
        $relatedModelName = Naming::modelName($relatedTable);

        return new HasMany(
            relatedTable: $relatedTable,
            relatedModel: 'App\\Models\\' . $relatedModelName,
            relatedModelName: $relatedModelName,
            relationMethodName: Str::camel($relatedTable),
            foreignKeyColumn: $foreignKeyColumn,
        );
    }
}
