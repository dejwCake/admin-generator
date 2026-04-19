<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Builders;

use Brackets\AdminGenerator\Builders\BelongsToBuilder;
use Brackets\AdminGenerator\Dtos\Relations\BelongsTo;
use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

final class BelongsToBuilderTest extends TestCase
{
    use DatabaseMigrations;

    public function testBuildReturnsBelongsToWithExpectedFieldsForCategoriesTable(): void
    {
        $belongsToBuilder = $this->app->make(BelongsToBuilder::class);

        $result = $belongsToBuilder->build('category_id', 'categories');

        self::assertInstanceOf(BelongsTo::class, $result);
        self::assertSame('category_id', $result->foreignKeyColumn);
        self::assertSame('categories', $result->relatedTable);
        self::assertSame('App\\Models\\Category', $result->relatedModel);
        self::assertSame('Category', $result->relatedModelName);
        self::assertSame('title', $result->relatedLabel);
        self::assertSame('category', $result->relationMethodName);
        self::assertSame('category-options', $result->optionsAttributeName);
        self::assertSame('categoryOptions', $result->optionsPropName);
    }

    public function testBuildHandlesSnakeCaseRelatedTable(): void
    {
        $belongsToBuilder = $this->app->make(BelongsToBuilder::class);

        $result = $belongsToBuilder->build('admin_user_id', 'admin_users');

        self::assertSame('adminUser', $result->relationMethodName);
        self::assertSame('admin-user-options', $result->optionsAttributeName);
        self::assertSame('adminUserOptions', $result->optionsPropName);
        self::assertSame('App\\Models\\AdminUser', $result->relatedModel);
        self::assertSame('AdminUser', $result->relatedModelName);
    }

    public function testBuildDerivesRelationMethodNameFromForeignKeyColumn(): void
    {
        $belongsToBuilder = $this->app->make(BelongsToBuilder::class);

        $result = $belongsToBuilder->build('parent_category_id', 'categories');

        self::assertSame('parentCategory', $result->relationMethodName);
    }
}
