<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class HasFormInputTest extends TestCase
{
    public function testReturnsFalseOnEmptyCollection(): void
    {
        $col = new ColumnCollection();

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyPasswordColumn(): void
    {
        $col = new ColumnCollection([self::makeColumn('password')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyEmailColumn(): void
    {
        $col = new ColumnCollection([self::makeColumn('email')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyJsonMajorType(): void
    {
        $col = new ColumnCollection([self::makeColumn('data', majorType: 'json')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyTextMajorType(): void
    {
        $col = new ColumnCollection([self::makeColumn('notes', majorType: 'text')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyBoolMajorType(): void
    {
        $col = new ColumnCollection([self::makeColumn('enabled', majorType: 'bool')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyDateMajorType(): void
    {
        $col = new ColumnCollection([self::makeColumn('published_at', majorType: 'date')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyTimeMajorType(): void
    {
        $col = new ColumnCollection([self::makeColumn('start_time', majorType: 'time')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyDatetimeMajorType(): void
    {
        $col = new ColumnCollection([self::makeColumn('created_at', majorType: 'datetime')]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsFalseWhenOnlyForeignKeyColumns(): void
    {
        $col = new ColumnCollection([self::makeColumn('user_id', majorType: 'integer', isForeignKey: true)]);

        self::assertFalse($col->hasFormInput());
    }

    public function testReturnsTrueWhenPlainStringColumnPresent(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('password'),
            self::makeColumn('email'),
            self::makeColumn('notes', majorType: 'text'),
            self::makeColumn('user_id', majorType: 'integer', isForeignKey: true),
            self::makeColumn('name'),
        ]);

        self::assertTrue($col->hasFormInput());
    }

    public function testReturnsTrueWhenIntegerNonForeignKeyColumnPresent(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('views', majorType: 'integer', isForeignKey: false),
        ]);

        self::assertTrue($col->hasFormInput());
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private static function makeColumn(
        string $name = 'title',
        string $majorType = 'string',
        bool $isForeignKey = false,
    ): Column {
        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: 'string',
            faker: 'word()',
            required: false,
            defaultTranslation: $name,
            isForeignKey: $isForeignKey,
            priority: null,
            serverStoreRules: new Collection(),
            serverUpdateRules: new Collection(),
            frontendRules: new Collection(),
        );
    }
}
