<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class NameFiltersTest extends TestCase
{
    public function testFilterByNameKeepsOnlyMatchingColumns(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('slug'),
            self::makeColumn('views', majorType: 'integer'),
        ]);

        $result = $collection->filterByName('title', 'views');

        self::assertSame(2, $result->count());
        self::assertArrayNotHasKey('slug', $result->toArray());
    }

    public function testRejectByNameRemovesMatchingColumns(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('slug'),
            self::makeColumn('views', majorType: 'integer'),
        ]);

        $result = $collection->rejectByName('slug', 'views');

        self::assertSame(1, $result->count());
        self::assertArrayHasKey('title', $result->toArray());
    }

    public function testHasByNameReturnsTrueWhenPresent(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title')]);

        self::assertTrue($collection->hasByName('title'));
    }

    public function testHasByNameReturnsFalseWhenAbsent(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title')]);

        self::assertFalse($collection->hasByName('slug'));
    }

    public function testHasByMajorTypeReturnsTrueWhenPresent(): void
    {
        $collection = new ColumnCollection([self::makeColumn('data', majorType: 'json')]);

        self::assertTrue($collection->hasByMajorType('json'));
    }

    public function testHasByMajorTypeReturnsFalseWhenAbsent(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title', majorType: 'string')]);

        self::assertFalse($collection->hasByMajorType('json'));
    }

    public function testIsNotEmptyReturnsTrueWhenNotEmpty(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title')]);

        self::assertTrue($collection->isNotEmpty());
    }

    public function testIsNotEmptyReturnsFalseWhenEmpty(): void
    {
        $collection = new ColumnCollection();

        self::assertFalse($collection->isNotEmpty());
    }

    private static function makeColumn(string $name, string $majorType = 'string'): Column
    {
        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: 'string',
            faker: 'word()',
            required: false,
            defaultTranslation: $name,
            isForeignKey: false,
            priority: null,
            serverStoreRules: new Collection(),
            serverUpdateRules: new Collection(),
            frontendRules: new Collection(),
        );
    }
}
