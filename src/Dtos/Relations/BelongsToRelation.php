<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

final readonly class BelongsToRelation
{
    public function __construct(
        public string $relatedTable,
        public string $relatedModel,
        public string $optionsPropName,
        public string $foreignKeyLabel,
    ) {
    }

    /** @deprecated just for compatibility with old code */
    public function toLegacyArray(): array
    {
        return [
            'relatedTable' => $this->relatedTable,
            'relatedModel' => $this->relatedModel,
            'optionsPropName' => $this->optionsPropName,
            'foreignKeyLabel' => $this->foreignKeyLabel,
        ];
    }
}
