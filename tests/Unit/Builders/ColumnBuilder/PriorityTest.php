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

final class PriorityTest extends TestCase
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

    public function testPriorityForName(): void
    {
        self::assertSame(0, $this->buildColumn(name: 'name')->priority);
    }

    public function testPriorityForTitle(): void
    {
        self::assertSame(0, $this->buildColumn(name: 'title')->priority);
    }

    public function testPriorityForLastName(): void
    {
        self::assertSame(0, $this->buildColumn(name: 'last_name')->priority);
    }

    public function testPriorityForSubject(): void
    {
        self::assertSame(0, $this->buildColumn(name: 'subject')->priority);
    }

    public function testPriorityForFirstName(): void
    {
        self::assertSame(1, $this->buildColumn(name: 'first_name')->priority);
    }

    public function testPriorityForEmail(): void
    {
        self::assertSame(1, $this->buildColumn(name: 'email')->priority);
    }

    public function testPriorityForAuthor(): void
    {
        self::assertSame(1, $this->buildColumn(name: 'author')->priority);
    }

    public function testPriorityForId(): void
    {
        self::assertSame(2, $this->buildColumn(name: 'id')->priority);
    }

    public function testPriorityForPublishedAt(): void
    {
        self::assertSame(3, $this->buildColumn(name: 'published_at')->priority);
    }

    public function testPriorityNullForOtherColumns(): void
    {
        self::assertNull($this->buildColumn(name: 'description')->priority);
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
