<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class UpdateRequestTest extends TestCase
{
    public function testUpdateRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/UpdateCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testUpdateRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Billing/Cat/UpdateCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testUpdateRequestGeneratorWithFullModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Cat/UpdateCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testUpdateRequestGeneratorWithFullNamespaceShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/UpdateCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
            '--model-with-full-namespace' => 'App\\Billing\\Category',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
