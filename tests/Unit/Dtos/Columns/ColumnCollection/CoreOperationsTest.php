<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class CoreOperationsTest extends TestCase
{
    public function testPushAddsColumn(): void
    {
        $collection = new ColumnCollection();
        $collection->push(self::makeColumn('name'));

        self::assertSame(1, $collection->count());
    }

    public function testToArrayReturnsKeyedArray(): void
    {
        $collection = new ColumnCollection([self::makeColumn('email')]);

        self::assertIsArray($collection->toArray());
        self::assertArrayHasKey('email', $collection->toArray());
    }

    public function testGetIteratorIsTraversable(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title'), self::makeColumn('slug')]);

        $names = [];
        foreach ($collection as $key => $column) {
            $names[] = $key;
        }

        self::assertSame(['title', 'slug'], $names);
    }

    public function testPluckReturnsValues(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title', majorType: 'string'),
            self::makeColumn('views', majorType: 'integer'),
        ]);

        $types = $collection->pluck('majorType');

        self::assertSame(['string', 'integer'], $types->values()->all());
    }

    public function testFilterReturnFilteredCollection(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title', majorType: 'string'),
            self::makeColumn('views', majorType: 'integer'),
        ]);

        $filtered = $collection->filter(static fn (Column $c) => $c->majorType === 'string');

        self::assertSame(1, $filtered->count());
        self::assertArrayHasKey('title', $filtered->toArray());
    }

    public function testCountMatchesNumberOfPushedColumns(): void
    {
        $collection = new ColumnCollection();
        $collection->push(self::makeColumn('a'));
        $collection->push(self::makeColumn('b'));
        $collection->push(self::makeColumn('c'));

        self::assertSame(3, $collection->count());
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
