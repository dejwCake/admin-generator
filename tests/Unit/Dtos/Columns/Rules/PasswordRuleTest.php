<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\Rules;

use Brackets\AdminGenerator\Dtos\Columns\Rules\PasswordRule;
use PHPUnit\Framework\TestCase;

final class PasswordRuleTest extends TestCase
{
    public function testToStringWithLength8(): void
    {
        $rule = new PasswordRule(8);

        $expected = 'Password::min(8)' . PHP_EOL .
            '                    ->letters()' . PHP_EOL .
            '                    ->mixedCase()' . PHP_EOL .
            '                    ->numbers()' . PHP_EOL .
            '                    ->symbols()' . PHP_EOL .
            '                    ->uncompromised()';

        self::assertSame($expected, (string) $rule);
    }

    public function testToStringWithLength16(): void
    {
        $rule = new PasswordRule(16);

        $output = (string) $rule;

        self::assertStringStartsWith('Password::min(16)', $output);
    }

    public function testToStringContainsAllChainedCalls(): void
    {
        $rule = new PasswordRule(8);
        $output = (string) $rule;

        $passwordMin = strpos($output, 'Password::min(8)');
        $passwordLetters = strpos($output, '->letters()');
        $passwordMixedCase = strpos($output, '->mixedCase()');
        $passwordNumbers = strpos($output, '->numbers()');
        $passwordSymbols = strpos($output, '->symbols()');
        $passwordUncompromise = strpos($output, '->uncompromised()');

        self::assertNotFalse($passwordMin);
        self::assertNotFalse($passwordLetters);
        self::assertNotFalse($passwordMixedCase);
        self::assertNotFalse($passwordNumbers);
        self::assertNotFalse($passwordSymbols);
        self::assertNotFalse($passwordUncompromise);

        // Assert order
        self::assertLessThan($passwordLetters, $passwordMin);
        self::assertLessThan($passwordMixedCase, $passwordLetters);
        self::assertLessThan($passwordNumbers, $passwordMixedCase);
        self::assertLessThan($passwordSymbols, $passwordNumbers);
        self::assertLessThan($passwordUncompromise, $passwordSymbols);
    }

    public function testToStringEndsWithUncompromised(): void
    {
        $rule = new PasswordRule(12);
        $output = (string) $rule;

        self::assertStringEndsWith('->uncompromised()', $output);
    }
}
