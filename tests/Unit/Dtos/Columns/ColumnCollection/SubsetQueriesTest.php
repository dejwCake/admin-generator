<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class SubsetQueriesTest extends TestCase
{
    public function testGetVisibleRejectsSystemColumns(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('id'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('updated_at', majorType: 'datetime'),
            self::makeColumn('deleted_at', majorType: 'datetime'),
            self::makeColumn('remember_token'),
            self::makeColumn('last_login_at', majorType: 'datetime'),
            self::makeColumn('title'),
        ]);

        $visible = $collection->getVisible();

        self::assertCount(1, $visible);
        self::assertArrayHasKey('title', $visible->toArray());
    }

    public function testGetFillableRejectsSystemColumnsButKeepsLastLoginAt(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('id'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('updated_at', majorType: 'datetime'),
            self::makeColumn('deleted_at', majorType: 'datetime'),
            self::makeColumn('remember_token'),
            self::makeColumn('last_login_at', majorType: 'datetime'),
            self::makeColumn('title'),
        ]);

        $fillable = $collection->getFillable();

        self::assertCount(2, $fillable);
        self::assertArrayHasKey('last_login_at', $fillable->toArray());
        self::assertArrayHasKey('title', $fillable->toArray());
    }

    public function testGetToSearchInIncludesJsonTextStringAndId(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('id', majorType: 'integer'),
            self::makeColumn('title', majorType: 'string'),
            self::makeColumn('body', majorType: 'text'),
            self::makeColumn('data', majorType: 'json'),
            self::makeColumn('views', majorType: 'integer'),
            self::makeColumn('password', majorType: 'string'),
            self::makeColumn('remember_token', majorType: 'string'),
        ]);

        $names = array_keys($collection->getToSearchIn()->toArray());

        self::assertContains('id', $names);
        self::assertContains('title', $names);
        self::assertContains('body', $names);
        self::assertContains('data', $names);
        self::assertNotContains('views', $names);
        self::assertNotContains('password', $names);
        self::assertNotContains('remember_token', $names);
    }

    public function testGetToExportRejectsSensitiveAndTimestampColumns(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('password'),
            self::makeColumn('remember_token'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('updated_at', majorType: 'datetime'),
            self::makeColumn('deleted_at', majorType: 'datetime'),
        ]);

        $result = $collection->getToExport();

        self::assertCount(1, $result);
        self::assertArrayHasKey('title', $result->toArray());
    }

    public function testGetForIndexFiltersOnlyColumnsWithPriority(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title', priority: 1),
            self::makeColumn('slug', priority: null),
            self::makeColumn('views', priority: 2),
        ]);

        $result = $collection->getForIndex();

        self::assertCount(2, $result);
        self::assertArrayHasKey('title', $result->toArray());
        self::assertArrayHasKey('views', $result->toArray());
        self::assertArrayNotHasKey('slug', $result->toArray());
    }

    private static function makeColumn(string $name, string $majorType = 'string', ?int $priority = null,): Column
    {
        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: 'string',
            faker: 'word()',
            required: false,
            defaultTranslation: $name,
            isForeignKey: false,
            priority: $priority,
            serverStoreRules: new Collection(),
            serverUpdateRules: new Collection(),
            frontendRules: new Collection(),
        );
    }
}
