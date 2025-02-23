<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateRequestTest extends TestCase
{
    use DatabaseMigrations;

    public function testUpdateRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/UpdateCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testUpdateRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/UpdateCat.php');

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
        $filePath = base_path('app/Http/Requests/Admin/Cat/UpdateCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:update', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
