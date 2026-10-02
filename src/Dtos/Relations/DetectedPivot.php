<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

final readonly class DetectedPivot
{
    public function __construct(
        public string $pivotTable,
        public string $relatedTable,
        public string $foreignKey,
        public string $relatedKey,
    ) {
    }
}
