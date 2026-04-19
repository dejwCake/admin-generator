<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\Rules;

use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use PHPUnit\Framework\TestCase;

final class UniqueRuleTest extends TestCase
{
    public function testDefaultWithNoOptions(): void
    {
        $rule = new UniqueRule(tableName: 'users', columnName: 'email', modelVariableName: null);

        self::assertSame("Rule::unique('users', 'email')", (string) $rule);
    }

    public function testWithLocaleOption(): void
    {
        $rule = new UniqueRule(tableName: 'posts', columnName: 'slug', modelVariableName: null, locale: true);

        self::assertSame("Rule::unique('posts', 'slug'->'.\$locale)", (string) $rule);
    }

    public function testWithIgnoreOption(): void
    {
        $rule = new UniqueRule(tableName: 'users', columnName: 'email', modelVariableName: 'adminUser', ignore: true);

        $expected = "Rule::unique('users', 'email')" . PHP_EOL .
            '                    ->ignore($this->adminUser->getKey(), $this->adminUser->getKeyName())';

        self::assertSame($expected, (string) $rule);
    }

    public function testWithDeletedAtOption(): void
    {
        $rule = new UniqueRule(tableName: 'users', columnName: 'email', modelVariableName: null, deletedAt: true);

        $expected = "Rule::unique('users', 'email')" . PHP_EOL .
            "                    ->whereNull('deleted_at')";

        self::assertSame($expected, (string) $rule);
    }

    public function testCombinedLocaleIgnoreDeletedAt(): void
    {
        $rule = new UniqueRule(
            tableName: 'posts',
            columnName: 'slug',
            modelVariableName: 'post',
            locale: true,
            deletedAt: true,
            ignore: true,
        );

        $expected = "Rule::unique('posts', 'slug'->'.\$locale)" . PHP_EOL .
            '                    ->ignore($this->post->getKey(), $this->post->getKeyName())' . PHP_EOL .
            "                    ->whereNull('deleted_at')";

        self::assertSame($expected, (string) $rule);
    }
}
