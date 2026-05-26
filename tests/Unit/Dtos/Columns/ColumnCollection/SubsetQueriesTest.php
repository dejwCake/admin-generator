<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Builders\ColumnBuilder;
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

    public function testGetArrayColumnsReturnsOnlyNonTranslatableJson(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('tags', majorType: 'json', isTranslatable: false),
            self::makeColumn('body', majorType: 'json', isTranslatable: true),
            self::makeColumn('title'),
        ]);

        $result = $collection->getArrayColumns();

        self::assertCount(1, $result);
        self::assertArrayHasKey('tags', $result->toArray());
    }

    public function testGetTranslatableUsesIsTranslatableFlag(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('tags', majorType: 'json', isTranslatable: false),
            self::makeColumn('body', majorType: 'json', isTranslatable: true),
            self::makeColumn('title'),
        ]);

        $result = $collection->getTranslatable();

        self::assertCount(1, $result);
        self::assertArrayHasKey('body', $result->toArray());
        self::assertArrayNotHasKey('tags', $result->toArray());
    }

    public function testGetNonTranslatableIncludesNonTranslatableJson(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('tags', majorType: 'json', isTranslatable: false),
            self::makeColumn('body', majorType: 'json', isTranslatable: true),
            self::makeColumn('title'),
        ]);

        $result = $collection->getNonTranslatable()->toArray();

        self::assertArrayHasKey('tags', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayNotHasKey('body', $result);
    }

    public function testGetForIndexIncludesNonTranslatableJson(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('tags', majorType: 'json', priority: 1, isTranslatable: false),
            self::makeColumn('body', majorType: 'json', priority: 2, isTranslatable: true),
            self::makeColumn('title', priority: 0),
            self::makeColumn('slug', priority: null),
        ]);

        $result = $collection->getForIndex();

        self::assertArrayHasKey('tags', $result->toArray());
        self::assertArrayHasKey('body', $result->toArray());
        self::assertArrayHasKey('title', $result->toArray());
        self::assertArrayNotHasKey('slug', $result->toArray());
    }

    public function testGetToSearchInExcludesNonTranslatableJson(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('tags', majorType: 'json', isTranslatable: false),
            self::makeColumn('body', majorType: 'json', isTranslatable: true),
        ]);

        $names = array_keys($collection->getToSearchIn()->toArray());

        self::assertContains('body', $names);
        self::assertNotContains('tags', $names);
    }

    public function testHasTagInputDetectsNonTranslatableJson(): void
    {
        self::assertTrue(
            (new ColumnCollection([self::makeColumn('tags', majorType: 'json', isTranslatable: false)]))->hasTagInput(),
        );
        self::assertFalse(
            (new ColumnCollection([self::makeColumn('body', majorType: 'json', isTranslatable: true)]))->hasTagInput(),
        );
    }

    public function testHasTranslatableDetectsTranslatableJson(): void
    {
        self::assertTrue(
            (new ColumnCollection(
                [self::makeColumn('body', majorType: 'json', isTranslatable: true)],
            ))->hasTranslatable(),
        );
        self::assertFalse(
            (new ColumnCollection(
                [self::makeColumn('tags', majorType: 'json', isTranslatable: false)],
            ))->hasTranslatable(),
        );
    }

    private static function makeColumn(
        string $name,
        string $majorType = 'string',
        ?int $priority = null,
        ?bool $isTranslatable = null,
    ): Column {
        $resolvedTranslatable = $isTranslatable ?? ($majorType === 'json');

        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: 'string',
            isTranslatable: $resolvedTranslatable,
            isWysiwyg: in_array($name, ColumnBuilder::WYSIWYG_COLUMN_NAMES, true)
                && ($majorType === 'text' || ($majorType === 'json' && $resolvedTranslatable)),
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
