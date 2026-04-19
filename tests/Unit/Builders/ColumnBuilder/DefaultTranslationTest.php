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

final class DefaultTranslationTest extends TestCase
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

    public function testDefaultTranslationForId(): void
    {
        self::assertSame('ID', $this->buildColumn(name: 'id')->defaultTranslation);
    }

    public function testDefaultTranslationStripsIdSuffix(): void
    {
        self::assertSame('Category', $this->buildColumn(name: 'category_id')->defaultTranslation);
    }

    public function testDefaultTranslationStripsIdSuffixWithUnderscores(): void
    {
        self::assertSame('Blog post', $this->buildColumn(name: 'blog_post_id')->defaultTranslation);
    }

    public function testDefaultTranslationUcfirstWithUnderscoresToSpaces(): void
    {
        self::assertSame('First name', $this->buildColumn(name: 'first_name')->defaultTranslation);
    }

    public function testDefaultTranslationSingleWord(): void
    {
        self::assertSame('Title', $this->buildColumn(name: 'title')->defaultTranslation);
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
