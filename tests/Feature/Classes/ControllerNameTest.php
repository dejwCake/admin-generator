<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ControllerNameTest extends TestCase
{
    use DatabaseMigrations;

    public function testControllerShouldBeGeneratedUnderDefaultNamespace(): void
    {
        $filePath = base_path('app/Http/Controllers/Admin/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testControllerNameCanBeNamespaced(): void
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

    public function testYouCanGenerateControllerOutsideDefaultDirectory(): void
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

    public function testYouCanPassAModelClassName(): void
    {
        $filePath = base_path('app/Http/Controllers/Billing/CategoriesController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:controller', [
            'table_name' => 'categories',
            'class_name' => 'App\\Http\\Controllers\\Billing\\CategoriesController',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
