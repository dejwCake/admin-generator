<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class TypeBasedQueriesTest extends TestCase
{
    public function testGetTranslatableReturnsJsonMajorType(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('data', majorType: 'json'),
            self::makeColumn('title', majorType: 'string'),
        ]);

        $result = $collection->getTranslatable();

        self::assertCount(1, $result);
        self::assertArrayHasKey('data', $result->toArray());
    }

    public function testGetNonTranslatableRejectsJsonAndId(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('id', majorType: 'integer'),
            self::makeColumn('data', majorType: 'json'),
            self::makeColumn('title', majorType: 'string'),
        ]);

        $result = $collection->getNonTranslatable();

        self::assertCount(1, $result);
        self::assertArrayHasKey('title', $result->toArray());
    }

    public function testGetBooleanReturnsBoolMajorType(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('enabled', majorType: 'bool'),
            self::makeColumn('title', majorType: 'string'),
        ]);

        $result = $collection->getBoolean();

        self::assertCount(1, $result);
        self::assertArrayHasKey('enabled', $result->toArray());
    }

    public function testGetDatesReturnsDatetimeAndDate(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('published_at', majorType: 'date'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('title', majorType: 'string'),
        ]);

        $result = $collection->getDates();

        self::assertCount(2, $result);
        self::assertArrayHasKey('published_at', $result->toArray());
        self::assertArrayHasKey('created_at', $result->toArray());
    }

    public function testGetHiddenReturnsPasswordAndRememberToken(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('password'),
            self::makeColumn('remember_token'),
            self::makeColumn('title'),
        ]);

        $result = $collection->getHidden();

        self::assertCount(2, $result);
        self::assertArrayHasKey('password', $result->toArray());
        self::assertArrayHasKey('remember_token', $result->toArray());
    }

    public function testGetWysiwygColumnNamesReturnsConst(): void
    {
        $collection = new ColumnCollection();

        self::assertSame(['perex', 'text', 'body', 'description'], $collection->getWysiwygColumnNames());
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
