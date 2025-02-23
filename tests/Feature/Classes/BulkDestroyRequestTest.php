<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BulkDestroyRequestTest extends TestCase
{
    use DatabaseMigrations;

    public function testBulkDestroyRequestGenerationShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/BulkDestroyCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:bulk-destroy', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testBulkDestroyRequestWithClassNameGenerationShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/BulkDestroyDog.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:bulk-destroy', [
            'table_name' => 'categories',
            'class_name' => 'BulkDestroyDog',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testBulkDestroyRequestWithModelNameGenerationShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/BulkDestroyCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:bulk-destroy', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
