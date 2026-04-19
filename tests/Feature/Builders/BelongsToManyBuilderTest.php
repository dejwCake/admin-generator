<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders;

use Brackets\AdminGenerator\Builders\BelongsToManyBuilder;
use Brackets\AdminGenerator\Dtos\Relations\BelongsToMany;
use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;

final class BelongsToManyBuilderTest extends TestCase
{
    use DatabaseMigrations;

    public function testBuildReturnsBelongsToManyWithExpectedFieldsForCategoriesAndPosts(): void
    {
        $belongsToManyBuilder = $this->app->make(BelongsToManyBuilder::class);

        $result = $belongsToManyBuilder->build('categories', 'posts');

        self::assertInstanceOf(BelongsToMany::class, $result);
        self::assertSame('categories', $result->relatedTable);
        self::assertSame('App\\Models\\Category', $result->relatedModel);
        self::assertSame('Category', $result->relatedModelName);
        self::assertSame('title', $result->relatedLabel);
        self::assertSame('category_post', $result->relationTable);
        self::assertSame('categories', $result->relationMethodName);
        self::assertSame('categories', $result->relationTranslationKey);
        self::assertSame('Categories', $result->relationTranslationValue);
        self::assertSame('category-options', $result->optionsAttributeName);
        self::assertSame('categoryOptions', $result->optionsPropName);
        self::assertSame('post_id', $result->foreignKey);
        self::assertSame('category_id', $result->relatedKey);
    }

    public function testBuildSortsRelationTableAlphabeticallyRegardlessOfArgumentOrder(): void
    {
        $belongsToManyBuilder = $this->app->make(BelongsToManyBuilder::class);

        $resultOne = $belongsToManyBuilder->build('categories', 'posts');
        $resultTwo = $belongsToManyBuilder->build('posts', 'categories');

        self::assertSame('category_post', $resultOne->relationTable);
        self::assertSame('category_post', $resultTwo->relationTable);
    }

    public function testBuildUsesSpatieRoleModelForRolesTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('roles', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });

        $belongsToManyBuilder = $this->app->make(BelongsToManyBuilder::class);

        $result = $belongsToManyBuilder->build('roles', 'admin_users');

        self::assertSame('Spatie\\Permission\\Models\\Role', $result->relatedModel);
        self::assertSame('Role', $result->relatedModelName);
    }

    public function testBuildDerivesForeignAndRelatedKeysFromTableSingulars(): void
    {
        $belongsToManyBuilder = $this->app->make(BelongsToManyBuilder::class);

        $result = $belongsToManyBuilder->build('categories', 'admin_users');

        self::assertSame('admin_user_id', $result->foreignKey);
        self::assertSame('category_id', $result->relatedKey);
    }
}
