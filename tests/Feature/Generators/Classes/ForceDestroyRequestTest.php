<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ForceDestroyRequestTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:force-destroy', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/ForceDestroyCategory.php');

        $this->artisan('admin:generate:request:force-destroy', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:request:force-destroy', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public function testGeneratorSkipsTheClassWithoutSoftDeletes(): void
    {
        $this->artisan('admin:generate:request:force-destroy', ['table_name' => 'posts'])
            ->expectsOutput('Skipping ForceDestroyPost: posts has no deleted_at column.');

        self::assertFileDoesNotExist($this->app->basePath('app/Http/Requests/Admin/Post/ForceDestroyPost.php'));
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/ForceDestroyCategory.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Billing/Cat/ForceDestroyCat.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Requests/Admin/Cat/ForceDestroyCat.php',
        ];

        yield 'categories with model-with-full-namespace App\\Billing\\Category' => [
            'arguments' => [
                'table_name' => 'categories',
                '--model-with-full-namespace' => 'App\\Billing\\Category',
            ],
            'expectedFilePath' => 'app/Http/Requests/Admin/Category/ForceDestroyCategory.php',
        ];
    }
}
