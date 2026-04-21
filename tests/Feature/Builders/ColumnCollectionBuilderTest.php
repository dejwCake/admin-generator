<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders;

use Brackets\AdminGenerator\Builders\ColumnCollectionBuilder;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Dtos\Columns\Rules\ServerUpdateRule;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;

final class ColumnCollectionBuilderTest extends TestCase
{
    use DatabaseMigrations;

    public function testBuildForCategoriesTableReturnsColumnCollection(): void
    {
        $builder = $this->app->make(ColumnCollectionBuilder::class);

        $result = $builder->build('categories');

        self::assertInstanceOf(ColumnCollection::class, $result);
        self::assertTrue($result->hasByName('id'));
        self::assertTrue($result->hasByName('title'));
        self::assertTrue($result->hasByName('slug'));
        self::assertTrue($result->hasByName('enabled'));
        self::assertTrue($result->hasByName('created_at'));
        self::assertTrue($result->hasByName('deleted_at'));
    }

    public function testBuildDerivedModelVariableNameFromTableName(): void
    {
        $builder = $this->app->make(ColumnCollectionBuilder::class);

        // categories table has 'slug' which is always unique → triggers UniqueRule with ignore
        $result = $builder->build('categories');

        $slugColumn = $result->toArray()['slug'] ?? null;
        self::assertNotNull($slugColumn);

        $uniqueRule = $slugColumn->serverUpdateRules->first(
            static fn (ServerUpdateRule $rule): bool => $rule instanceof UniqueRule,
        );
        self::assertNotNull($uniqueRule);

        // modelVariableName derived from 'categories' = 'category'
        self::assertStringContainsString('->ignore($this->category->', (string) $uniqueRule);
    }

    public function testBuildWithExplicitModelVariableNamePassesThroughToUniqueRule(): void
    {
        $builder = $this->app->make(ColumnCollectionBuilder::class);

        $result = $builder->build('categories', 'myCategory');

        $slugColumn = $result->toArray()['slug'] ?? null;
        self::assertNotNull($slugColumn);

        $uniqueRule = $slugColumn->serverUpdateRules->first(
            static fn (ServerUpdateRule $rule): bool => $rule instanceof UniqueRule,
        );
        self::assertNotNull($uniqueRule);

        self::assertStringContainsString('->ignore($this->myCategory->', (string) $uniqueRule);
    }

    public function testBuildDetectsSoftDeleteAndAddsWhereNullDeletedAtToUniqueRule(): void
    {
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();

        $schemaBuilder->create('soft_delete_items', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('code');
            $table->softDeletes();
            $table->unique('code', 'soft_delete_items_code_null_deleted_at_unique');
        });

        $builder = $this->app->make(ColumnCollectionBuilder::class);
        $result = $builder->build('soft_delete_items');

        $codeColumn = $result->toArray()['code'] ?? null;
        self::assertNotNull($codeColumn);

        $uniqueRule = $codeColumn->serverUpdateRules->first(
            static fn (ServerUpdateRule $rule): bool => $rule instanceof UniqueRule,
        );
        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("->whereNull('deleted_at')", (string) $uniqueRule);
    }

    public function testAssignPrioritiesProducesContiguousSequenceForKnownColumns(): void
    {
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();

        $schemaBuilder->create('priority_items', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->text('body');
            $table->timestamps();
        });

        $builder = $this->app->make(ColumnCollectionBuilder::class);
        $result = $builder->build('priority_items');

        $columns = $result->toArray();

        // 'name' has fixed priority 0 → remapped to 0 (only fixed priority present initially)
        // 'email' has fixed priority 1 → remapped to 1
        // 'id' has fixed priority 2 → remapped to 2
        // 'body' is majorType=text, excluded from index-eligible → no priority
        // 'created_at', 'updated_at' are excluded → no priority
        self::assertSame(0, $columns['name']->priority);
        self::assertSame(1, $columns['email']->priority);
        self::assertSame(2, $columns['id']->priority);
        self::assertNull($columns['body']->priority);
        self::assertNull($columns['created_at']->priority);
        self::assertNull($columns['updated_at']->priority);
    }

    public function testAssignPrioritiesAssignsNextPriorityToColumnsWithoutFixedPriority(): void
    {
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();

        $schemaBuilder->create('mixed_priority_items', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('extra_field');
            $table->timestamps();
        });

        $builder = $this->app->make(ColumnCollectionBuilder::class);
        $result = $builder->build('mixed_priority_items');

        $columns = $result->toArray();

        // 'name' fixed priority 0 → remapped to 0
        // 'id' fixed priority 2 → remapped to 1
        // 'extra_field' has no fixed priority → assigned min(nextPriority=2, 10) = 2
        self::assertSame(0, $columns['name']->priority);
        self::assertSame(1, $columns['id']->priority);
        self::assertSame(2, $columns['extra_field']->priority);
    }
}
