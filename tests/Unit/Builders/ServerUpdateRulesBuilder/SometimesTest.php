<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ServerUpdateRulesBuilder;

use Brackets\AdminGenerator\Builders\ServerUpdateRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Rules\NullableRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\SometimesRule;
use PHPUnit\Framework\TestCase;

final class SometimesTest extends TestCase
{
    private readonly ServerUpdateRulesBuilder $serverUpdateRulesBuilder;

    protected function setUp(): void
    {
        $this->serverUpdateRulesBuilder = new ServerUpdateRulesBuilder();
    }

    public function testRequiredColumnHasSometimesRuleFirst(): void
    {
        $rules = $this->serverUpdateRulesBuilder->build(
            name: 'title',
            type: 'varchar',
            majorType: 'string',
            required: true,
            unique: false,
            tableName: 'articles',
            excludeDeletedAt: false,
            modelVariableName: 'article',
        );

        self::assertInstanceOf(SometimesRule::class, $rules->first());
    }

    public function testNotRequiredColumnHasNullableRuleFirst(): void
    {
        $rules = $this->serverUpdateRulesBuilder->build(
            name: 'title',
            type: 'varchar',
            majorType: 'string',
            required: false,
            unique: false,
            tableName: 'articles',
            excludeDeletedAt: false,
            modelVariableName: 'article',
        );

        self::assertInstanceOf(NullableRule::class, $rules->first());
    }
}
