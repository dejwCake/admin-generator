<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\Rules;

use Brackets\AdminGenerator\Dtos\Columns\Rules\BooleanRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ConfirmedRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\DateRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\EmailRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\IntegerRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\NullableRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\NumericRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\RequiredRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\SometimesRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\StringRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\TimeRule;
use PHPUnit\Framework\TestCase;

final class TrivialRulesTest extends TestCase
{
    public function testBooleanRule(): void
    {
        self::assertSame("'boolean'", (string) new BooleanRule());
    }

    public function testConfirmedRule(): void
    {
        self::assertSame("'confirmed'", (string) new ConfirmedRule());
    }

    public function testDateRule(): void
    {
        self::assertSame("'date'", (string) new DateRule());
    }

    public function testEmailRule(): void
    {
        self::assertSame("'email'", (string) new EmailRule());
    }

    public function testIntegerRule(): void
    {
        self::assertSame("'integer'", (string) new IntegerRule());
    }

    public function testNullableRule(): void
    {
        self::assertSame("'nullable'", (string) new NullableRule());
    }

    public function testNumericRule(): void
    {
        self::assertSame("'numeric'", (string) new NumericRule());
    }

    public function testRequiredRule(): void
    {
        self::assertSame("'required'", (string) new RequiredRule());
    }

    public function testSometimesRule(): void
    {
        self::assertSame("'sometimes'", (string) new SometimesRule());
    }

    public function testStringRule(): void
    {
        self::assertSame("'string'", (string) new StringRule());
    }

    public function testTimeRule(): void
    {
        self::assertSame("Rule::date()->format('H:i:s')", (string) new TimeRule());
    }
}
