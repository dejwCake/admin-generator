<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns;

use Brackets\AdminGenerator\Dtos\Columns\Rules\ServerStoreRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ServerUpdateRule;
use Illuminate\Support\Collection;

final readonly class Column
{
    /**
     * @param Collection<int, ServerStoreRule> $serverStoreRules
     * @param Collection<int, ServerUpdateRule> $serverUpdateRules
     * @param Collection<int, string> $frontendRules
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $majorType,
        public string $phpType,
        public string $faker,
        public bool $required,
        public bool $unique,
        public bool $hasUniqueDeleteAtIndex,
        public string $defaultTranslation,
        public Collection $serverStoreRules,
        public Collection $serverUpdateRules,
        public Collection $frontendRules,
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
            'uniqueDeletedAtCondition' => $this->hasUniqueDeleteAtIndex,
            'defaultTranslation' => $this->defaultTranslation,
            'serverStoreRules' => $this->serverStoreRules
                ->map(static fn (ServerStoreRule $serverStoreRule) => (string) $serverStoreRule)
                ->toArray(),
            'serverUpdateRules' => $this->serverUpdateRules
                ->map(static fn (ServerUpdateRule $serverUpdateRule) => (string) $serverUpdateRule)
                ->toArray(),
            'frontendRules' => $this->frontendRules
                ->toArray(),
        ];
    }
}
