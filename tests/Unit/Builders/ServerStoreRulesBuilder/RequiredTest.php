<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ServerStoreRulesBuilder;

use Brackets\AdminGenerator\Builders\ServerStoreRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Rules\NullableRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\RequiredRule;
use PHPUnit\Framework\TestCase;

final class RequiredTest extends TestCase
{
    private ServerStoreRulesBuilder $serverStoreRulesBuilder;

    protected function setUp(): void
    {
        $this->serverStoreRulesBuilder = new ServerStoreRulesBuilder();
    }

    public function testRequiredColumnHasRequiredRuleFirst(): void
    {
        $rules = $this->serverStoreRulesBuilder->build(
            name: 'title',
            type: 'varchar',
            majorType: 'string',
            required: true,
            unique: false,
            tableName: 'articles',
            excludeDeletedAt: false,
        );

        self::assertInstanceOf(RequiredRule::class, $rules->first());
    }

    public function testNotRequiredColumnHasNullableRuleFirst(): void
    {
        $rules = $this->serverStoreRulesBuilder->build(
            name: 'title',
            type: 'varchar',
            majorType: 'string',
            required: false,
            unique: false,
            tableName: 'articles',
            excludeDeletedAt: false,
        );

        self::assertInstanceOf(NullableRule::class, $rules->first());
    }
}
