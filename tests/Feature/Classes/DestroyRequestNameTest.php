<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DestroyRequestNameTest extends TestCase
{
    use DatabaseMigrations;

    public function testDestroyRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/DestroyCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testDestroyRequestGeneratorWithClassNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/DestroyDog.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
            'class_name' => 'DestroyDog',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testDestroyRequestGeneratorWithClassNameFullShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Billing/DestroyDog.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
            'class_name' => 'App\\Http\\Requests\\Billing\\DestroyDog',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testDestroyRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/DestroyCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
