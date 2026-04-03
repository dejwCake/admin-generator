<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns;

final readonly class Column
{
    public function __construct(
        public string $name,
        public string $type,
        public string $majorType,
        public string $phpType,
        public string $faker,
        public bool $required,
        public bool $unique,
        public bool $uniqueDeletedAtCondition,
        public string $defaultTranslation,
    ) {
    }

    /** @deprecated just for compatibility with old code */
    public function toLegacyArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'majorType' => $this->majorType,
            'phpType' => $this->phpType,
            'faker' => $this->faker,
            'required' => $this->required,
            'unique' => $this->unique,
            'uniqueDeletedAtCondition' => $this->uniqueDeletedAtCondition,
            'defaultTranslation' => $this->defaultTranslation,
        ];
    }
}
