<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class GetLabelColumnTest extends TestCase
{
    public function testReturnsTitleWhenPresent(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('name'),
            self::makeColumn('email'),
        ]);

        self::assertSame('title', $col->getLabelColumn());
    }

    public function testReturnsNameWhenTitleNotPresent(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('name'),
            self::makeColumn('first_name'),
            self::makeColumn('email'),
        ]);

        self::assertSame('name', $col->getLabelColumn());
    }

    public function testReturnsFirstNameWhenTitleAndNameNotPresent(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('first_name'),
            self::makeColumn('email'),
        ]);

        self::assertSame('first_name', $col->getLabelColumn());
    }

    public function testReturnsEmailWhenTitleNameFirstNameNotPresent(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('email'),
            self::makeColumn('views', majorType: 'integer'),
        ]);

        self::assertSame('email', $col->getLabelColumn());
    }

    public function testTitleBeatsAllOtherPreferred(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('name'),
            self::makeColumn('first_name'),
            self::makeColumn('email'),
        ]);

        self::assertSame('title', $col->getLabelColumn());
    }

    public function testFallsBackToFirstStringColumnWhenNoPreferredPresent(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('views', majorType: 'integer'),
            self::makeColumn('slug', majorType: 'string'),
        ]);

        self::assertSame('slug', $col->getLabelColumn());
    }

    public function testFallsBackToIdWhenNoPreferredAndNoStringColumn(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('views', majorType: 'integer'),
            self::makeColumn('enabled', majorType: 'bool'),
        ]);

        self::assertSame('id', $col->getLabelColumn());
    }

    public function testFallsBackToIdOnEmptyCollection(): void
    {
        $col = new ColumnCollection();

        self::assertSame('id', $col->getLabelColumn());
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private static function makeColumn(string $name = 'title', string $majorType = 'string',): Column
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
