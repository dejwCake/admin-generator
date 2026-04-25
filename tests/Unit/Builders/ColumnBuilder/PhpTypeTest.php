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

final class PhpTypeTest extends TestCase
{
    private readonly ColumnBuilder $columnBuilder;

    protected function setUp(): void
    {
        $this->columnBuilder = new ColumnBuilder(
            new ServerStoreRulesBuilder(),
            new ServerUpdateRulesBuilder(),
            new FrontendRulesBuilder(),
        );
    }

    public function testPhpTypeInteger(): void
    {
        self::assertSame('int', $this->buildColumn(type: 'integer')->phpType);
    }

    public function testPhpTypeFloat(): void
    {
        self::assertSame('float', $this->buildColumn(type: 'decimal')->phpType);
    }

    public function testPhpTypeBool(): void
    {
        self::assertSame('bool', $this->buildColumn(type: 'boolean')->phpType);
    }

    public function testPhpTypeDatetime(): void
    {
        self::assertSame('CarbonInterface', $this->buildColumn(type: 'datetime')->phpType);
    }

    public function testPhpTypeDate(): void
    {
        self::assertSame('CarbonInterface', $this->buildColumn(type: 'date')->phpType);
    }

    public function testPhpTypeJson(): void
    {
        self::assertSame('array', $this->buildColumn(type: 'json')->phpType);
    }

    public function testPhpTypeString(): void
    {
        self::assertSame('string', $this->buildColumn(type: 'varchar')->phpType);
    }

    public function testPhpTypeTextDefaultsToString(): void
    {
        self::assertSame('string', $this->buildColumn(type: 'mediumtext')->phpType);
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
