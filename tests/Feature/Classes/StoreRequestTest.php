<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StoreRequestTest extends TestCase
{
    use DatabaseMigrations;

    public function testStoreRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/StoreCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:store', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testStoreRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/StoreCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:store', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testStoreRequestGeneratorWithFullModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Cat/StoreCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:store', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testStoreRequestGeneratorWithBelongsToManyShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/StoreCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:store', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
