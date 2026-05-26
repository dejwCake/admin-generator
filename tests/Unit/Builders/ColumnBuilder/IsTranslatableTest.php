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

final class IsTranslatableTest extends TestCase
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

    public function testJsonWithoutListIsTranslatable(): void
    {
        self::assertTrue($this->buildColumn('data', 'json')->isTranslatable);
    }

    public function testJsonbWithoutListIsTranslatable(): void
    {
        self::assertTrue($this->buildColumn('data', 'jsonb')->isTranslatable);
    }

    public function testJsonInTranslatableListIsTranslatable(): void
    {
        self::assertTrue($this->buildColumn('title', 'json', ['title'])->isTranslatable);
    }

    public function testJsonNotInTranslatableListIsNotTranslatable(): void
    {
        self::assertFalse($this->buildColumn('title', 'json', ['other'])->isTranslatable);
    }

    public function testJsonWithEmptyListIsNotTranslatable(): void
    {
        self::assertFalse($this->buildColumn('title', 'json', [])->isTranslatable);
    }

    public function testNonJsonInListIsIgnored(): void
    {
        self::assertFalse($this->buildColumn('title', 'varchar', ['title'])->isTranslatable);
    }

    public function testNonJsonWithoutListIsNotTranslatable(): void
    {
        self::assertFalse($this->buildColumn('title', 'varchar')->isTranslatable);
    }

    private function buildColumn(string $name, string $type, ?array $translatable = null): Column
    {
        return $this->columnBuilder->build(
            name: $name,
            type: $type,
            nullable: true,
            tableName: 'articles',
            indexes: new Collection(),
            hasSoftDelete: false,
            modelVariableName: 'article',
            translatable: $translatable,
        );
    }
}
