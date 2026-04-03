<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Columns\Rules\BooleanRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ConfirmedRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\DateRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\EmailRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\IntegerRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\NullableRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\NumericRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\PasswordRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\RequiredRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ServerStoreRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\StringRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\TimeRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Illuminate\Support\Collection;

final class ServerStoreRulesBuilder
{
    /** @var Collection<int, ServerStoreRule> */
    private Collection $serverStoreRules;

    public function __construct()
    {
        $this->serverStoreRules = new Collection();
    }

    /** @return Collection<int, ServerStoreRule> */
    public function build(
        string $name,
        string $type,
        string $majorType,
        bool $required,
        bool $unique,
        string $tableName,
        bool $excludeDeletedAt,
    ): Collection {
        $this->serverStoreRules = new Collection();
        $this->buildByRequire($required);
        $this->buildByName($name);
        $this->getByUnique($name, $type, $unique, $tableName, $excludeDeletedAt);
        $this->getByMajorType($majorType);

        return $this->serverStoreRules;
    }

    private function buildByRequire(bool $required): void
    {
        if ($required) {
            $this->serverStoreRules->push(new RequiredRule());
        } else {
            $this->serverStoreRules->push(new NullableRule());
        }
    }

    private function buildByName(string $name): void
    {
        if ($name === 'email') {
            $this->serverStoreRules->push(new EmailRule());
        }

        if ($name === 'password') {
            $this->serverStoreRules->push(new ConfirmedRule());
            $this->serverStoreRules->push(new PasswordRule(8));
        }
    }

    private function getByUnique(
        string $name,
        string $type,
        bool $unique,
        string $tableName,
        bool $excludeDeletedAt,
    ): void {
        if ($unique || $name === 'slug') {
            $this->serverStoreRules->push(
                new UniqueRule(
                    $tableName,
                    $name,
                    null,
                    in_array($type, ['json', 'jsonb'], true),
                    $excludeDeletedAt,
                    false,
                ),
            );
        }
    }

    private function getByMajorType(string $majorType): void
    {
        $this->serverStoreRules->push($this->getRuleFromType($majorType));
    }

    private function getRuleFromType(string $majorType): ServerStoreRule
    {
        return match ($majorType) {
            'datetime',
            'date' => new DateRule(),
            'time' => new TimeRule(),
            'integer' => new IntegerRule(),
            'float' => new NumericRule(),
            'bool' => new BooleanRule(),
            default => new StringRule(),
        };
    }
}
