<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class BulkDestroyRequestTest extends TestCase
{
    public function testBulkDestroyRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/BulkDestroyCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:bulk-destroy', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testBulkDestroyRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Billing/Cat/BulkDestroyCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:bulk-destroy', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testBulkDestroyRequestGeneratorWithFullModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Cat/BulkDestroyCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:bulk-destroy', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
