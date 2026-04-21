<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class ControllerTest extends TestCase
{
    public function testControllerGeneratorShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerGeneratorWithClassNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/Billing/MyNameController.php');

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
        $filePath = $this->app->basePath('app/Http/Controllers/Billing/CategoriesController.php');

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
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');

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
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');

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
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');

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
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');

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
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
