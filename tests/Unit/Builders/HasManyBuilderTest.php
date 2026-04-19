<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders;

use Brackets\AdminGenerator\Builders\HasManyBuilder;
use PHPUnit\Framework\TestCase;

final class HasManyBuilderTest extends TestCase
{
    private HasManyBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new HasManyBuilder();
    }

    public function testSimpleSingularTable(): void
    {
        $hasMany = $this->builder->build('post_id', 'posts');

        self::assertSame('posts', $hasMany->relatedTable);
        self::assertSame('App\\Models\\Post', $hasMany->relatedModel);
        self::assertSame('Post', $hasMany->relatedModelName);
        self::assertSame('posts', $hasMany->relationMethodName);
        self::assertSame('post_id', $hasMany->foreignKeyColumn);
    }

    public function testAdminUserTable(): void
    {
        $hasMany = $this->builder->build('admin_user_id', 'admin_users');

        self::assertSame('admin_users', $hasMany->relatedTable);
        self::assertSame('App\\Models\\AdminUser', $hasMany->relatedModel);
        self::assertSame('AdminUser', $hasMany->relatedModelName);
        self::assertSame('adminUsers', $hasMany->relationMethodName);
        self::assertSame('admin_user_id', $hasMany->foreignKeyColumn);
    }

    public function testMultiWordTable(): void
    {
        $hasMany = $this->builder->build('blog_post_id', 'blog_posts');

        self::assertSame('blog_posts', $hasMany->relatedTable);
        self::assertSame('App\\Models\\BlogPost', $hasMany->relatedModel);
        self::assertSame('BlogPost', $hasMany->relatedModelName);
        self::assertSame('blogPosts', $hasMany->relationMethodName);
        self::assertSame('blog_post_id', $hasMany->foreignKeyColumn);
    }

    public function testRelatedModelNameIsSingular(): void
    {
        // 'categories' → 'Category'
        $hasMany = $this->builder->build('category_id', 'categories');

        self::assertSame('Category', $hasMany->relatedModelName);
        self::assertSame('App\\Models\\Category', $hasMany->relatedModel);
    }

    public function testRelationMethodNameIsCamelCaseOfTable(): void
    {
        $hasMany = $this->builder->build('order_item_id', 'order_items');

        self::assertSame('orderItems', $hasMany->relationMethodName);
    }
}
