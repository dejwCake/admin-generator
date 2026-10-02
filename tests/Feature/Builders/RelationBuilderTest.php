<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders;

use Brackets\AdminGenerator\Builders\RelationBuilder;
use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Database\Schema\Blueprint;

final class RelationBuilderTest extends TestCase
{
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

    public function testBuildExcludesAuditColumnsFromBelongsTo(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('categories', null);

        self::assertFalse($result->hasBelongsToByColumn('created_by_admin_user_id'));
        self::assertFalse($result->hasBelongsToByColumn('updated_by_admin_user_id'));
    }

    public function testBuildDetectsBelongsToWhenRelatedTableExists(): void
    {
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

    public function testBuildDoesNotTreatEntityWithTwoForeignKeysAsPivotTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create(
            'tickets',
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('post_id');
                $table->foreign('post_id')->references('id')->on('posts');
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');
                $table->string('subject');
                $table->timestamps();
            },
        );

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        self::assertFalse($result->hasRelatedTableInBelongsToMany('users'));
        self::assertFalse($result->isPivotTable('tickets'));
        // Not a pivot means it is simply a child of `posts`.
        self::assertTrue($result->getHasMany()->has('tickets'));
    }

    public function testBuildStillDetectsPivotTableCarryingOnlyItsForeignKeys(): void
    {
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
        $schemaBuilder->create('tags', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
        $schemaBuilder->create('post_tag', static function (Blueprint $table): void {
            $table->unsignedInteger('post_id');
            $table->foreign('post_id')->references('id')->on('posts');
            $table->unsignedInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags');
        });

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        self::assertTrue($result->hasRelatedTableInBelongsToMany('tags'));
        self::assertTrue($result->isPivotTable('post_tag'));
    }

    public function testBuildTreatsPivotTableWithTimestampsAsPivotTable(): void
    {
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
        $schemaBuilder->create('labels', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
        $schemaBuilder->create('label_post', static function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->foreign('post_id')->references('id')->on('posts');
            $table->unsignedInteger('label_id');
            $table->foreign('label_id')->references('id')->on('labels');
            $table->timestamps();
        });

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        // A surrogate key and timestamps are not payload -- this is still a pivot.
        self::assertTrue($result->hasRelatedTableInBelongsToMany('labels'));
    }

    public function testBuildUsesTheRealPivotTableNameWhenItDoesNotFollowTheConvention(): void
    {
        // The convention for (posts, crews) would be `crew_post`. Naming the pivot something
        // else used to be silently replaced by that invented name, pointing the generated
        // relation at a table that does not exist.
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
        $schemaBuilder->create('crews', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
        $schemaBuilder->create('post_crew_assignments', static function (Blueprint $table): void {
            $table->unsignedInteger('post_id');
            $table->foreign('post_id')->references('id')->on('posts');
            $table->unsignedInteger('crew_id');
            $table->foreign('crew_id')->references('id')->on('crews');
        });

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        $relation = $result->getBelongsToMany()->get('crews');
        self::assertNotNull($relation);
        self::assertSame('post_crew_assignments', $relation->relationTable);
        // isPivotTable() matches on relationTable, so the real name also keeps the pivot from
        // being picked up a second time as a hasMany child.
        self::assertTrue($result->isPivotTable('post_crew_assignments'));
        self::assertFalse($result->getHasMany()->has('post_crew_assignments'));
    }

    public function testBuildUsesTheRealForeignKeyColumnsOfThePivot(): void
    {
        // `writer_id` does not follow from the related table name, so the convention would have
        // guessed `author_id` and produced a relation that cannot resolve.
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
        $schemaBuilder->create('authors', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });
        $schemaBuilder->create('author_post', static function (Blueprint $table): void {
            $table->unsignedInteger('post_id');
            $table->foreign('post_id')->references('id')->on('posts');
            $table->unsignedInteger('writer_id');
            $table->foreign('writer_id')->references('id')->on('authors');
        });

        $relationBuilder = $this->app->make(RelationBuilder::class);

        $result = $relationBuilder->build('posts', null);

        $relation = $result->getBelongsToMany()->get('authors');
        self::assertNotNull($relation);
        self::assertSame('post_id', $relation->foreignKey);
        self::assertSame('writer_id', $relation->relatedKey);
        self::assertSame('author_post', $relation->relationTable);
    }

    public function testBuildWithExplicitBelongsToManyTableListFallsBackToTheConvention(): void
    {
        $relationBuilder = $this->app->make(RelationBuilder::class);

        // Nothing was discovered here, so the Laravel convention is all there is to go on.
        $result = $relationBuilder->build('posts', 'categories');

        $relation = $result->getBelongsToMany()->get('categories');
        self::assertNotNull($relation);
        self::assertSame('category_post', $relation->relationTable);
        self::assertSame('post_id', $relation->foreignKey);
        self::assertSame('category_id', $relation->relatedKey);
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
