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
        public string $majorType,
        public string $phpType,
        public string $faker,
        public bool $required,
        public string $defaultTranslation,
        public bool $isForeignKey,
        public ?int $priority,
        public Collection $serverStoreRules,
        public Collection $serverUpdateRules,
        public Collection $frontendRules,
    ) {
    }

    public function getFrontendValidationRule(): ?string
    {
        $rules = $this->frontendRules->filter()->values();

        if ($rules->isEmpty()) {
            return null;
        }

        return "'" . $rules->implode('|') . "'";
    }

    public function withPriority(?int $priority): self
    {
        return new self(
            name: $this->name,
            majorType: $this->majorType,
            phpType: $this->phpType,
            faker: $this->faker,
            required: $this->required,
            defaultTranslation: $this->defaultTranslation,
            isForeignKey: $this->isForeignKey,
            priority: $priority,
            serverStoreRules: $this->serverStoreRules,
            serverUpdateRules: $this->serverUpdateRules,
            frontendRules: $this->frontendRules,
        );
    }
}
