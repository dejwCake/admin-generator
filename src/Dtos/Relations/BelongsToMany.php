<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

final readonly class BelongsToMany
{
    public function __construct(
        public string $currentTable,
        public string $relatedTable,
        public string $relatedModel,
        public string $relatedModelName,
        public string $foreignKey,
        public string $relatedKey,
        public string $relatedLabel,
        public string $relationTable,
        public string $relationMethodName,
        public string $optionsPropName,
    ) {
    }
}
