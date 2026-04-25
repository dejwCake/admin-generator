<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ControllerTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');

        $this->artisan('admin:generate:controller', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with class_name Billing\\MyNameController' => [
            'arguments' => ['table_name' => 'categories', 'class_name' => 'Billing\\MyNameController'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/Billing/MyNameController.php',
        ];

        yield 'categories with class_name App\\Http\\Controllers\\Billing\\CategoriesController' => [
            'arguments' => [
                'table_name' => 'categories',
                'class_name' => 'App\\Http\\Controllers\\Billing\\CategoriesController',
            ],
            'expectedFilePath' => 'app/Http/Controllers/Billing/CategoriesController.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with model-with-full-namespace App\\Billing\\Category' => [
            'arguments' => [
                'table_name' => 'categories',
                '--model-with-full-namespace' => 'App\\Billing\\Category',
            ],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with belongs-to-many posts' => [
            'arguments' => ['table_name' => 'categories', '--belongs-to-many' => 'posts'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with with-export' => [
            'arguments' => ['table_name' => 'categories', '--with-export' => true],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories without bulk' => [
            'arguments' => ['table_name' => 'categories', '--without-bulk' => true],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with media' => [
            'arguments' => ['table_name' => 'categories', '--media' => ['gallery:image:public:5000']],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with template admin-user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'admin-user'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'categories with template user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'user'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesController.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/PostsController.php',
        ];

        yield 'posts with class_name Feed\\FeedController' => [
            'arguments' => ['table_name' => 'posts', 'class_name' => 'Feed\\FeedController'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/Feed/FeedController.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/PostsController.php',
        ];

        yield 'posts with model-with-full-namespace App\\Feed\\Post' => [
            'arguments' => [
                'table_name' => 'posts',
                '--model-with-full-namespace' => 'App\\Feed\\Post',
            ],
            'expectedFilePath' => 'app/Http/Controllers/Admin/PostsController.php',
        ];

        yield 'posts with belongs-to-many categories' => [
            'arguments' => ['table_name' => 'posts', '--belongs-to-many' => 'categories'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/PostsController.php',
        ];

        yield 'posts with with-export' => [
            'arguments' => ['table_name' => 'posts', '--with-export' => true],
            'expectedFilePath' => 'app/Http/Controllers/Admin/PostsController.php',
        ];

        yield 'posts without bulk' => [
            'arguments' => ['table_name' => 'posts', '--without-bulk' => true],
            'expectedFilePath' => 'app/Http/Controllers/Admin/PostsController.php',
        ];
    }
}
