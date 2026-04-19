<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Dtos\Columns\Rules\PasswordRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\TimeRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class UpdateRuleUsageTest extends TestCase
{
    public function testHasUpdateRuleUsageReturnsTrueWhenTimeRulePresent(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('start_time', serverUpdateRules: new Collection([new TimeRule()])),
        ]);

        self::assertTrue($collection->hasUpdateRuleUsage());
    }

    public function testHasUpdateRuleUsageReturnsTrueWhenUniqueRulePresent(): void
    {
        $rule = new UniqueRule('users', 'email', null);
        $collection = new ColumnCollection([
            self::makeColumn('email', serverUpdateRules: new Collection([$rule])),
        ]);

        self::assertTrue($collection->hasUpdateRuleUsage());
    }

    public function testHasUpdateRuleUsageReturnsFalseWhenNoTimeOrUniqueRule(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title')]);

        self::assertFalse($collection->hasUpdateRuleUsage());
    }

    public function testHasUpdatePasswordUsageReturnsTrueWhenPasswordRulePresent(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('password', serverUpdateRules: new Collection([new PasswordRule(8)])),
        ]);

        self::assertTrue($collection->hasUpdatePasswordUsage());
    }

    public function testHasUpdatePasswordUsageReturnsFalseWhenNoPasswordRule(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title')]);

        self::assertFalse($collection->hasUpdatePasswordUsage());
    }

    private static function makeColumn(string $name, ?Collection $serverUpdateRules = null): Column
    {
        return new Column(
            name: $name,
            majorType: 'string',
            phpType: 'string',
            faker: 'word()',
            required: false,
            defaultTranslation: $name,
            isForeignKey: false,
            priority: null,
            serverStoreRules: new Collection(),
            serverUpdateRules: $serverUpdateRules ?? new Collection(),
            frontendRules: new Collection(),
        );
    }
}
