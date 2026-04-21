<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ImpersonalLoginRequestTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:impersonal-login', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/ImpersonalLoginCategory.php');

        $this->artisan('admin:generate:request:impersonal-login', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:request:impersonal-login', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/ImpersonalLoginCategory.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Billing/Cat/ImpersonalLoginCat.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Cat/ImpersonalLoginCat.php',
        ];

        yield 'categories with model-with-full-namespace App\\Billing\\Category' => [
            'arguments' => [
                'table_name' => 'categories',
                '--model-with-full-namespace' => 'App\\Billing\\Category',
            ],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/ImpersonalLoginCategory.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Post/ImpersonalLoginPost.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Feed/Article/ImpersonalLoginArticle.php',
        ];

        yield 'posts with model-name App\\Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'App\\Feed\\Article'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Article/ImpersonalLoginArticle.php',
        ];

        yield 'posts with model-with-full-namespace App\\Feed\\Post' => [
            'arguments' => [
                'table_name' => 'posts',
                '--model-with-full-namespace' => 'App\\Feed\\Post',
            ],
            'expectedFilePath' => 'app/Http/Requests/Admin/Post/ImpersonalLoginPost.php',
        ];
    }
}
