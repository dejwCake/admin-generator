<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

final readonly class HasMany
{
    public function __construct(
        public string $relatedTable,
        public string $relatedModel,
        public string $relatedModelName,
        public string $relationMethodName,
        public string $foreignKeyColumn,
    ) {
    }
}
