<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class ConstructionTest extends TestCase
{
    public function testConstructorAcceptsArray(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title'), self::makeColumn('slug')]);

        self::assertSame(2, $collection->count());
    }

    public function testConstructorAcceptsCollection(): void
    {
        $collection = new ColumnCollection(new Collection([self::makeColumn('title')]));

        self::assertSame(1, $collection->count());
    }

    public function testConstructorFiltersNonColumnEntries(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title'), 'not-a-column', 42, null]);

        self::assertSame(1, $collection->count());
    }

    public function testConstructorKeysByName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('title'), self::makeColumn('slug')]);

        $array = $collection->toArray();
        self::assertArrayHasKey('title', $array);
        self::assertArrayHasKey('slug', $array);
    }

    private static function makeColumn(string $name): Column
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
            serverUpdateRules: new Collection(),
            frontendRules: new Collection(),
        );
    }
}
