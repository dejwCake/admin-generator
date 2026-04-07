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
        public string $relatedLabel,
        public string $relationMethodName,
        public string $optionsAttributeName,
        public string $optionsPropName,
    ) {
    }
}
