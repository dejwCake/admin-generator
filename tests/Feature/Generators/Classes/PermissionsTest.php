<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PermissionsTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $migrationFile): void
    {
        $this->artisan('admin:generate:permissions', $arguments);

        $filePath = $this->getPermissionMigrationPath($migrationFile);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $this->artisan('admin:generate:permissions', ['table_name' => 'categories']);

        $filePath = $this->getPermissionMigrationPath('fill_permissions_for_category.php');
        self::assertNotNull($filePath);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:permissions', [
            'table_name' => 'categories',
            '--force' => true,
        ]);

        $filePath = $this->getPermissionMigrationPath('fill_permissions_for_category.php');
        self::assertNotNull($filePath);
        self::assertFileExists($filePath);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'migrationFile' => 'fill_permissions_for_category.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'migrationFile' => 'fill_permissions_for_cat.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'migrationFile' => 'fill_permissions_for_cat.php',
        ];

        yield 'categories without bulk' => [
            'arguments' => ['table_name' => 'categories', '--without-bulk' => true],
            'migrationFile' => 'fill_permissions_for_category.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'migrationFile' => 'fill_permissions_for_post.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'migrationFile' => 'fill_permissions_for_article.php',
        ];

        yield 'posts without bulk' => [
            'arguments' => ['table_name' => 'posts', '--without-bulk' => true],
            'migrationFile' => 'fill_permissions_for_post.php',
        ];
    }
}
