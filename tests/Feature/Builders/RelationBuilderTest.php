<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders;

use Brackets\AdminGenerator\Builders\RelationBuilder;
use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;

final class RelationBuilderTest extends TestCase
{
    use DatabaseMigrations;

    public function testBuildForPostsTableDetectsBelongsToManyViaPivotTable(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        self::assertTrue($result->hasBelongsToMany());
        self::assertTrue($result->hasRelatedTableInBelongsToMany('categories'));
    }

    public function testBuildForCategoriesTableDetectsBelongsToManyViaPivotTable(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('categories', null);

        self::assertTrue($result->hasBelongsToMany());
        self::assertTrue($result->hasRelatedTableInBelongsToMany('posts'));
    }

    public function testBuildHasNoBelongsToWhenForeignTableMissing(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('categories', null);

        self::assertFalse($result->hasBelongsTo());
    }

    public function testBuildExcludesAuditColumnsFromBelongsTo(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('categories', null);

        self::assertFalse($result->hasBelongsToByColumn('created_by_admin_user_id'));
        self::assertFalse($result->hasBelongsToByColumn('updated_by_admin_user_id'));
    }

    public function testBuildDetectsBelongsToWhenRelatedTableExists(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('categories', null);

        self::assertTrue($result->hasBelongsTo());
        self::assertTrue($result->hasBelongsToByColumn('user_id'));
    }

    public function testBuildDetectsHasManyByExpectedForeignKey(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create(
            'comments',
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('post_id');
                $table->string('body');
            },
        );

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        self::assertTrue($result->hasHasMany());
        self::assertTrue($result->getHasMany()->has('comments'));
    }

    public function testBuildSkipsPivotTableWhenDetectingHasMany(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        self::assertFalse($result->getHasMany()->has('category_post'));
    }

    public function testBuildWithExplicitBelongsToManyTableListAddsRelation(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('admin_users', 'categories');

        self::assertTrue($result->hasBelongsToMany());
        self::assertTrue($result->hasRelatedTableInBelongsToMany('categories'));
    }

    public function testBuildWithExplicitBelongsToManyTableListIgnoresUnknownTable(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('admin_users', 'nonexistent_table');

        self::assertFalse($result->hasBelongsToMany());
    }

    public function testBuildReturnsEmptyRelationsForTableWithoutAnyRelations(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create(
            'isolated_items',
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->string('label');
            },
        );

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('isolated_items', null);

        self::assertFalse($result->hasBelongsTo());
        self::assertFalse($result->hasBelongsToMany());
        self::assertFalse($result->hasHasMany());
    }
}
