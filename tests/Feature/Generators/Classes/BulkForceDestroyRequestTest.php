<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class BulkForceDestroyRequestTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:bulk-force-destroy', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/BulkForceDestroyCategory.php');

        $this->artisan('admin:generate:request:bulk-force-destroy', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:request:bulk-force-destroy', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public function testGeneratorSkipsTheClassWithoutSoftDeletes(): void
    {
        $this->artisan('admin:generate:request:bulk-force-destroy', ['table_name' => 'posts'])
            ->expectsOutput('Skipping BulkForceDestroyPost: posts has no deleted_at column.');

        self::assertFileDoesNotExist($this->app->basePath('app/Http/Requests/Admin/Post/BulkForceDestroyPost.php'));
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/BulkForceDestroyCategory.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Billing/Cat/BulkForceDestroyCat.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Cat/BulkForceDestroyCat.php',
        ];

        yield 'categories with model-with-full-namespace App\\Billing\\Category' => [
            'arguments' => [
                'table_name' => 'categories',
                '--model-with-full-namespace' => 'App\\Billing\\Category',
            ],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/BulkForceDestroyCategory.php',
        ];
    }
}
