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

final class FakerByMajorTypeTest extends TestCase
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

    public function testFakerIntegerMajorTypeDefault(): void
    {
        self::assertSame('$this->faker->randomNumber(5)', $this->buildColumn(name: 'count', type: 'integer')->faker);
    }

    public function testFakerBoolMajorTypeDefault(): void
    {
        self::assertSame('$this->faker->boolean()', $this->buildColumn(name: 'active', type: 'boolean')->faker);
    }

    public function testFakerDateMajorTypeDefault(): void
    {
        self::assertSame('$this->faker->date()', $this->buildColumn(name: 'birthday', type: 'date')->faker);
    }

    public function testFakerDatetimeMajorTypeDefault(): void
    {
        self::assertSame('$this->faker->dateTime', $this->buildColumn(name: 'published', type: 'datetime')->faker);
    }

    public function testFakerTimeMajorTypeDefault(): void
    {
        self::assertSame('$this->faker->time()', $this->buildColumn(name: 'starts_at', type: 'time')->faker);
    }

    public function testFakerTextMajorTypeDefault(): void
    {
        self::assertSame('$this->faker->text()', $this->buildColumn(name: 'content', type: 'mediumtext')->faker);
    }

    public function testFakerStringMajorTypeDefaultsToSentence(): void
    {
        self::assertSame('$this->faker->sentence', $this->buildColumn(name: 'description', type: 'varchar')->faker);
    }

    private function buildColumn(string $name, string $type): Column
    {
        return $this->columnBuilder->build(
            name: $name,
            type: $type,
            nullable: true,
            tableName: 'articles',
            indexes: new Collection(),
            hasSoftDelete: false,
            modelVariableName: 'article',
        );
    }
}
