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

final class MiscColumnPropertiesTest extends TestCase
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

    public function testRequiredIsTrueWhenNotNullable(): void
    {
        self::assertTrue($this->buildColumn(nullable: false)->required);
    }

    public function testRequiredIsFalseWhenNullable(): void
    {
        self::assertFalse($this->buildColumn(nullable: true)->required);
    }

    public function testIntegerNonForeignColumnHasIntegerInFrontendRules(): void
    {
        $column = $this->buildColumn(name: 'count', type: 'integer');

        self::assertContains('integer', $column->frontendRules->all());
    }

    private function buildColumn(string $name = 'title', string $type = 'varchar', bool $nullable = true,): Column
    {
        return $this->columnBuilder->build(
            name: $name,
            type: $type,
            nullable: $nullable,
            tableName: 'articles',
            indexes: new Collection(),
            hasSoftDelete: false,
            modelVariableName: 'article',
        );
    }
}
