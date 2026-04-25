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

final class IsForeignKeyTest extends TestCase
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

    public function testIsForeignKeyForColumnEndingInId(): void
    {
        self::assertTrue($this->buildColumn(name: 'user_id')->isForeignKey);
    }

    public function testIsNotForeignKeyForCreatedByAdminUserId(): void
    {
        self::assertFalse($this->buildColumn(name: 'created_by_admin_user_id')->isForeignKey);
    }

    public function testIsNotForeignKeyForUpdatedByAdminUserId(): void
    {
        self::assertFalse($this->buildColumn(name: 'updated_by_admin_user_id')->isForeignKey);
    }

    public function testIsNotForeignKeyForColumnNotEndingInId(): void
    {
        self::assertFalse($this->buildColumn(name: 'title')->isForeignKey);
    }

    private function buildColumn(string $name): Column
    {
        return $this->columnBuilder->build(
            name: $name,
            type: 'varchar',
            nullable: true,
            tableName: 'articles',
            indexes: new Collection(),
            hasSoftDelete: false,
            modelVariableName: 'article',
        );
    }
}
