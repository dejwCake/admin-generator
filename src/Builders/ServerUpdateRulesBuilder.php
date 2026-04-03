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
use Brackets\AdminGenerator\Dtos\Columns\Rules\ServerUpdateRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\SometimesRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\StringRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\TimeRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Illuminate\Support\Collection;

final class ServerUpdateRulesBuilder
{
    /** @var Collection<int, ServerUpdateRule> */
    private Collection $serverUpdateRules;

    public function __construct()
    {
        $this->serverUpdateRules = new Collection();
    }

    /** @return Collection<int, ServerUpdateRule> */
    public function build(
        string $name,
        string $type,
        string $majorType,
        bool $required,
        bool $unique,
        string $tableName,
        bool $excludeDeletedAt,
        string $modelVariableName,
    ): Collection {
        $this->serverUpdateRules = new Collection();
        $this->buildByRequire($required);
        $this->buildByName($name);
        $this->getByUnique($name, $type, $unique, $tableName, $excludeDeletedAt, $modelVariableName);
        $this->getByMajorType($majorType);

        return $this->serverUpdateRules;
    }

    private function buildByRequire(bool $required): void
    {
        if ($required) {
            $this->serverUpdateRules->push(new SometimesRule());
        } else {
            $this->serverUpdateRules->push(new NullableRule());
        }
    }

    private function buildByName(string $name): void
    {
        if ($name === 'email') {
            $this->serverUpdateRules->push(new EmailRule());
        }

        if ($name === 'password') {
            $this->serverUpdateRules->push(new ConfirmedRule());
            $this->serverUpdateRules->push(new PasswordRule(8));
        }
    }

    private function getByUnique(
        string $name,
        string $type,
        bool $unique,
        string $tableName,
        bool $excludeDeletedAt,
        string $modelVariableName,
    ): void {
        if ($unique || $name === 'slug') {
            $this->serverUpdateRules->push(
                new UniqueRule(
                    $tableName,
                    $name,
                    $modelVariableName,
                    in_array($type, ['json', 'jsonb'], true),
                    $excludeDeletedAt,
                    true,
                ),
            );
        }
    }

    private function getByMajorType(string $majorType): void
    {
        $this->serverUpdateRules->push($this->getRuleFromType($majorType));
    }

    private function getRuleFromType(string $majorType): ServerUpdateRule
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
