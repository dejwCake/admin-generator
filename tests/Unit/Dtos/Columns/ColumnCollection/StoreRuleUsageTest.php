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

final class StoreRuleUsageTest extends TestCase
{
    public function testHasStoreRuleUsageReturnsTrueWhenTimeRulePresent(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('start_time', serverStoreRules: new Collection([new TimeRule()])),
        ]);

        self::assertTrue($collection->hasStoreRuleUsage());
    }

    public function testHasStoreRuleUsageReturnsTrueWhenUniqueRulePresent(): void
    {
        $rule = new UniqueRule('users', 'email', null);
        $collection = new ColumnCollection([
            self::makeColumn('email', serverStoreRules: new Collection([$rule])),
        ]);

        self::assertTrue($collection->hasStoreRuleUsage());
    }

    public function testHasStoreRuleUsageReturnsFalseWhenNoTimeOrUniqueRule(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title')]);

        self::assertFalse($collection->hasStoreRuleUsage());
    }

    public function testHasStorePasswordUsageReturnsTrueWhenPasswordRulePresent(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('password', serverStoreRules: new Collection([new PasswordRule(8)])),
        ]);

        self::assertTrue($collection->hasStorePasswordUsage());
    }

    public function testHasStorePasswordUsageReturnsFalseWhenNoPasswordRule(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title')]);

        self::assertFalse($collection->hasStorePasswordUsage());
    }

    private static function makeColumn(string $name, ?Collection $serverStoreRules = null): Column
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
            serverStoreRules: $serverStoreRules ?? new Collection(),
            serverUpdateRules: new Collection(),
            frontendRules: new Collection(),
        );
    }
}
