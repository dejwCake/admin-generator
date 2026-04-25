<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class UpdateRequestTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/UpdateCategory.php');

        $this->artisan('admin:generate:request:update', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/UpdateCategory.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Billing/Cat/UpdateCat.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Cat/UpdateCat.php',
        ];

        yield 'categories with model-with-full-namespace App\\Billing\\Category' => [
            'arguments' => [
                'table_name' => 'categories',
                '--model-with-full-namespace' => 'App\\Billing\\Category',
            ],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/UpdateCategory.php',
        ];

        yield 'categories with belongs-to-many posts' => [
            'arguments' => ['table_name' => 'categories', '--belongs-to-many' => 'posts'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/UpdateCategory.php',
        ];

        yield 'categories with template admin-user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'admin-user'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/UpdateCategory.php',
        ];

        yield 'categories with template user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'user'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/UpdateCategory.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Post/UpdatePost.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Feed/Article/UpdateArticle.php',
        ];

        yield 'posts with model-with-full-namespace App\\Feed\\Post' => [
            'arguments' => [
                'table_name' => 'posts',
                '--model-with-full-namespace' => 'App\\Feed\\Post',
            ],
            'expectedFilePath' => 'app/Http/Requests/Admin/Post/UpdatePost.php',
        ];

        yield 'posts with belongs-to-many categories' => [
            'arguments' => ['table_name' => 'posts', '--belongs-to-many' => 'categories'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Post/UpdatePost.php',
        ];
    }
}
