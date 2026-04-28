<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Illuminate\Support\Collection;

final class FrontendRulesBuilder
{
    /** @var Collection<int, string> */
    private Collection $frontendRules;

    public function __construct()
    {
        $this->frontendRules = new Collection();
    }

    /** @return Collection<int, string> */
    public function build(string $name, string $majorType, bool $required, bool $isForeignKey): Collection
    {
        $this->frontendRules = new Collection();

        $this->buildByRequire($name, $majorType, $required);
        $this->buildByName($name);
        $this->buildByMajorType($majorType, $isForeignKey);

        return $this->frontendRules->unique();
    }

    private function buildByRequire(string $name, string $majorType, bool $required): void
    {
        if ($required && $majorType !== 'bool' && $name !== 'password') {
            $this->frontendRules->push('required');
        }
    }

    private function buildByName(string $name): void
    {
        if ($name === 'email') {
            $this->frontendRules->push('email');
        }
    }

    private function buildByMajorType(string $majorType, bool $isForeignKey): void
    {
        if ($isForeignKey) {
            return;
        }

        $rule = match ($majorType) {
            'integer' => 'integer',
            'bool' => '',
            default => null,
        };

        if ($rule !== null) {
            $this->frontendRules->push($rule);
        }
    }
}
