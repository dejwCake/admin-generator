<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class VueFormTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateComponent(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:vue-form', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteComponent(): void
    {
        $filePath = $this->app->basePath('resources/js/admin/category/Form.vue');

        $this->artisan('admin:generate:vue-form', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'resources/js/admin/category/Form.vue',
        ];

        yield 'categories with model-name Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\CategOry'],
            'expectedFilePath' => 'resources/js/admin/billing-categ-ory/Form.vue',
        ];

        yield 'categories with model-name App\\Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\CategOry'],
            'expectedFilePath' => 'resources/js/admin/categ-ory/Form.vue',
        ];

        yield 'categories with belongs-to-many posts' => [
            'arguments' => ['table_name' => 'categories', '--belongs-to-many' => 'posts'],
            'expectedFilePath' => 'resources/js/admin/category/Form.vue',
        ];

        yield 'categories with template admin-user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'admin-user'],
            'expectedFilePath' => 'resources/js/admin/category/Form.vue',
        ];

        yield 'categories with template user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'user'],
            'expectedFilePath' => 'resources/js/admin/category/Form.vue',
        ];

        yield 'categories with media gallery' => [
            'arguments' => ['table_name' => 'categories', '--media' => ['gallery:image:public:5000']],
            'expectedFilePath' => 'resources/js/admin/category/Form.vue',
        ];

        yield 'categories with file-name profile/edit-password' => [
            'arguments' => ['table_name' => 'categories', '--file-name' => 'profile/edit-password'],
            'expectedFilePath' => 'resources/js/admin/profile-edit-password/Form.vue',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'resources/js/admin/post/Form.vue',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'resources/js/admin/feed-article/Form.vue',
        ];

        yield 'posts with model-name App\\Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'App\\Feed\\Article'],
            'expectedFilePath' => 'resources/js/admin/article/Form.vue',
        ];

        yield 'posts with belongs-to-many categories' => [
            'arguments' => ['table_name' => 'posts', '--belongs-to-many' => 'categories'],
            'expectedFilePath' => 'resources/js/admin/post/Form.vue',
        ];
    }
}
