<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ServerUpdateRulesBuilder;

use Brackets\AdminGenerator\Builders\ServerUpdateRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ConfirmedRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\EmailRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\PasswordRule;
use PHPUnit\Framework\TestCase;

final class NameBasedTest extends TestCase
{
    private ServerUpdateRulesBuilder $serverUpdateRulesBuilder;

    protected function setUp(): void
    {
        $this->serverUpdateRulesBuilder = new ServerUpdateRulesBuilder();
    }

    public function testEmailNameAddsEmailRule(): void
    {
        $rules = $this->serverUpdateRulesBuilder->build(
            name: 'email',
            type: 'varchar',
            majorType: 'string',
            required: false,
            unique: false,
            tableName: 'articles',
            excludeDeletedAt: false,
            modelVariableName: 'article',
        );

        $hasEmail = $rules->contains(static fn ($rule): bool => $rule instanceof EmailRule);
        self::assertTrue($hasEmail);
    }

    public function testPasswordNameAddsConfirmedAndPasswordRules(): void
    {
        $rules = $this->serverUpdateRulesBuilder->build(
            name: 'password',
            type: 'varchar',
            majorType: 'string',
            required: false,
            unique: false,
            tableName: 'articles',
            excludeDeletedAt: false,
            modelVariableName: 'article',
        );

        $hasConfirmed = $rules->contains(static fn ($rule): bool => $rule instanceof ConfirmedRule);
        $hasPassword = $rules->contains(static fn ($rule): bool => $rule instanceof PasswordRule);

        self::assertTrue($hasConfirmed);
        self::assertTrue($hasPassword);
    }
}
