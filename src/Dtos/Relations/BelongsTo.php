<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

final readonly class BelongsTo
{
    public function __construct(
        public string $foreignKeyColumn,
        public string $relatedTable,
        public string $relatedModel,
        public string $relatedModelName,
        public string $optionsPropName,
        public string $foreignKeyLabel,
        public string $relationMethodName,
    ) {
    }
}
