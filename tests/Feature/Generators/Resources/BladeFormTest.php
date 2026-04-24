<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class BladeFormTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateView(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:blade-form', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteView(): void
    {
        $filePath = $this->app->basePath('resources/views/admin/category/form.blade.php');

        $this->artisan('admin:generate:blade-form', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'resources/views/admin/category/form.blade.php',
        ];

        yield 'categories with model-name Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\CategOry'],
            'expectedFilePath' => 'resources/views/admin/billing/categ-ory/form.blade.php',
        ];

        yield 'categories with model-name App\\Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\CategOry'],
            'expectedFilePath' => 'resources/views/admin/categ-ory/form.blade.php',
        ];

        yield 'categories with belongs-to-many posts' => [
            'arguments' => ['table_name' => 'categories', '--belongs-to-many' => 'posts'],
            'expectedFilePath' => 'resources/views/admin/category/form.blade.php',
        ];

        yield 'categories with file-name profile/edit-password' => [
            'arguments' => ['table_name' => 'categories', '--file-name' => 'profile/edit-password'],
            'expectedFilePath' => 'resources/views/admin/profile/edit-password.blade.php',
        ];

        yield 'categories with route admin/categ-ories' => [
            'arguments' => ['table_name' => 'categories', '--route' => 'admin/categ-ories'],
            'expectedFilePath' => 'resources/views/admin/category/form.blade.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'resources/views/admin/post/form.blade.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'resources/views/admin/feed/article/form.blade.php',
        ];

        yield 'posts with model-name App\\Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'App\\Feed\\Article'],
            'expectedFilePath' => 'resources/views/admin/article/form.blade.php',
        ];

        yield 'posts with belongs-to-many categories' => [
            'arguments' => ['table_name' => 'posts', '--belongs-to-many' => 'categories'],
            'expectedFilePath' => 'resources/views/admin/post/form.blade.php',
        ];
    }
}
