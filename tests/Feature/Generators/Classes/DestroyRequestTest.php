<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class DestroyRequestTest extends TestCase
{
    public function testDestroyRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/DestroyCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testDestroyRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Billing/Cat/DestroyCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testDestroyRequestGeneratorWithFullModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Cat/DestroyCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testDestroyRequestGeneratorWithFullNamespaceShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/DestroyCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:destroy', [
            'table_name' => 'categories',
            '--model-with-full-namespace' => 'App\\Billing\\Category',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
