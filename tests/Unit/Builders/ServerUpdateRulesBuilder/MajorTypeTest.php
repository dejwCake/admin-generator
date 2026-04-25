<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ServerUpdateRulesBuilder;

use Brackets\AdminGenerator\Builders\ServerUpdateRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Rules\BooleanRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\DateRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\IntegerRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\NumericRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\StringRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\TimeRule;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class MajorTypeTest extends TestCase
{
    private readonly ServerUpdateRulesBuilder $serverUpdateRulesBuilder;

    protected function setUp(): void
    {
        $this->serverUpdateRulesBuilder = new ServerUpdateRulesBuilder();
    }

    public function testDatetimeMajorTypeAddsDateRule(): void
    {
        self::assertInstanceOf(DateRule::class, $this->buildWithMajorType('datetime')->last());
    }

    public function testDateMajorTypeAddsDateRule(): void
    {
        self::assertInstanceOf(DateRule::class, $this->buildWithMajorType('date')->last());
    }

    public function testTimeMajorTypeAddsTimeRule(): void
    {
        self::assertInstanceOf(TimeRule::class, $this->buildWithMajorType('time')->last());
    }

    public function testIntegerMajorTypeAddsIntegerRule(): void
    {
        self::assertInstanceOf(IntegerRule::class, $this->buildWithMajorType('integer')->last());
    }

    public function testFloatMajorTypeAddsNumericRule(): void
    {
        self::assertInstanceOf(NumericRule::class, $this->buildWithMajorType('float')->last());
    }

    public function testBoolMajorTypeAddsBooleanRule(): void
    {
        self::assertInstanceOf(BooleanRule::class, $this->buildWithMajorType('bool')->last());
    }

    public function testDefaultMajorTypeAddsStringRule(): void
    {
        self::assertInstanceOf(StringRule::class, $this->buildWithMajorType('string')->last());
    }

    public function testTextMajorTypeAddsStringRule(): void
    {
        self::assertInstanceOf(StringRule::class, $this->buildWithMajorType('text')->last());
    }

    private function buildWithMajorType(string $majorType): Collection
    {
        return $this->serverUpdateRulesBuilder->build(
            name: 'field',
            type: 'varchar',
            majorType: $majorType,
            required: false,
            unique: false,
            tableName: 'articles',
            excludeDeletedAt: false,
            modelVariableName: 'article',
        );
    }
}
