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

final class FakerByNameTest extends TestCase
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

    public function testFakerDeletedAt(): void
    {
        self::assertSame('null', $this->buildColumn(name: 'deleted_at')->faker);
    }

    public function testFakerRememberToken(): void
    {
        self::assertSame('null', $this->buildColumn(name: 'remember_token')->faker);
    }

    public function testFakerEmail(): void
    {
        self::assertSame('$this->faker->email', $this->buildColumn(name: 'email')->faker);
    }

    public function testFakerName(): void
    {
        self::assertSame('$this->faker->firstName', $this->buildColumn(name: 'name')->faker);
    }

    public function testFakerFirstName(): void
    {
        self::assertSame('$this->faker->firstName', $this->buildColumn(name: 'first_name')->faker);
    }

    public function testFakerSurname(): void
    {
        self::assertSame('$this->faker->lastName', $this->buildColumn(name: 'surname')->faker);
    }

    public function testFakerLastName(): void
    {
        self::assertSame('$this->faker->lastName', $this->buildColumn(name: 'last_name')->faker);
    }

    public function testFakerSlug(): void
    {
        self::assertSame('$this->faker->unique()->slug', $this->buildColumn(name: 'slug')->faker);
    }

    public function testFakerPassword(): void
    {
        self::assertSame('$hasher->make($this->faker->password)', $this->buildColumn(name: 'password')->faker);
    }

    public function testFakerLanguage(): void
    {
        self::assertSame("'en'", $this->buildColumn(name: 'language')->faker);
    }

    public function testFakerPrice(): void
    {
        self::assertSame('$this->faker->randomFloat(2, max: 10000)', $this->buildColumn(name: 'price')->faker);
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
