<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ColumnBuilder;

use Brackets\AdminGenerator\Builders\ColumnBuilder;
use Brackets\AdminGenerator\Builders\FrontendRulesBuilder;
use Brackets\AdminGenerator\Builders\ServerStoreRulesBuilder;
use Brackets\AdminGenerator\Builders\ServerUpdateRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Column;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class MajorTypeTest extends TestCase
{
    private ColumnBuilder $columnBuilder;

    protected function setUp(): void
    {
        $this->columnBuilder = new ColumnBuilder(
            new ServerStoreRulesBuilder(),
            new ServerUpdateRulesBuilder(),
            new FrontendRulesBuilder(),
        );
    }

    public function testMajorTypeDatetime(): void
    {
        self::assertSame('datetime', $this->buildColumn(type: 'datetime')->majorType);
    }

    public function testMajorTypeTimestamp(): void
    {
        self::assertSame('datetime', $this->buildColumn(type: 'timestamp')->majorType);
    }

    public function testMajorTypeDate(): void
    {
        self::assertSame('date', $this->buildColumn(type: 'date')->majorType);
    }

    public function testMajorTypeTime(): void
    {
        self::assertSame('time', $this->buildColumn(type: 'time')->majorType);
    }

    public function testMajorTypeIntegerFromInt(): void
    {
        self::assertSame('integer', $this->buildColumn(type: 'int')->majorType);
    }

    public function testMajorTypeIntegerFromBigint(): void
    {
        self::assertSame('integer', $this->buildColumn(type: 'bigint')->majorType);
    }

    public function testMajorTypeIntegerFromSmallint(): void
    {
        self::assertSame('integer', $this->buildColumn(type: 'smallint')->majorType);
    }

    public function testMajorTypeFloatFromDecimal(): void
    {
        self::assertSame('float', $this->buildColumn(type: 'decimal')->majorType);
    }

    public function testMajorTypeFloatFromDouble(): void
    {
        self::assertSame('float', $this->buildColumn(type: 'double')->majorType);
    }

    public function testMajorTypeBoolFromTinyint(): void
    {
        self::assertSame('bool', $this->buildColumn(type: 'tinyint')->majorType);
    }

    public function testMajorTypeBoolFromBoolean(): void
    {
        self::assertSame('bool', $this->buildColumn(type: 'boolean')->majorType);
    }

    public function testMajorTypeJsonFromLongtext(): void
    {
        self::assertSame('json', $this->buildColumn(type: 'longtext')->majorType);
    }

    public function testMajorTypeJsonFromJson(): void
    {
        self::assertSame('json', $this->buildColumn(type: 'json')->majorType);
    }

    public function testMajorTypeStringFromVarchar(): void
    {
        self::assertSame('string', $this->buildColumn(type: 'varchar')->majorType);
    }

    public function testMajorTypeTextFromMediumtext(): void
    {
        self::assertSame('text', $this->buildColumn(type: 'mediumtext')->majorType);
    }

    public function testMajorTypeTextFromUnknown(): void
    {
        self::assertSame('text', $this->buildColumn(type: 'unknowntype')->majorType);
    }

    private function buildColumn(string $type = 'varchar'): Column
    {
        return $this->columnBuilder->build(
            name: 'title',
            type: $type,
            nullable: true,
            tableName: 'articles',
            indexes: new Collection(),
            hasSoftDelete: false,
            modelVariableName: 'article',
        );
    }
}
