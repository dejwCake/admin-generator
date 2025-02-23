<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ControllerNameTest extends TestCase
{
    use DatabaseMigrations;

    public function testControllerGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithClassNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/Billing/MyNameController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            'class_name' => 'Billing\\MyNameController',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithFullClassNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Billing/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            'class_name' => 'App\\Http\\Controllers\\Billing\\CategoriesController',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithBelongsToManyShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithModelWithFullNamespaceShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            '--model-with-full-namespace' => 'App\\Billing\\Category',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithExportShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithoutBulkShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
