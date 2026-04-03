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
    public function build(string $name, string $majorType, bool $required,): Collection
    {
        $this->frontendRules = new Collection();
        $this->buildByRequire($name, $majorType, $required);
        $this->buildByName($name);

        $this->buildByMajorType($majorType);

        return $this->frontendRules;
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

        if ($name === 'password') {
            $this->frontendRules->push('confirmed:password');
            $this->frontendRules->push('min:8');
            //TODO not working, need fixing
//            $this->frontendRules->push('regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!$#%]).*$/g');
        }
    }

    private function buildByMajorType(string $majorType): void
    {
        $rule = match ($majorType) {
            'datetime',
            'date' => 'date_format:yyyy-MM-dd HH:mm:ss',
            'time' => 'date_format:HH:mm:ss',
            'integer' => 'integer',
            'float' => 'numeric',
            'bool' => '',
            default => null,
        };

        if ($rule !== null) {
            $this->frontendRules->push($rule);
        }
    }
}
