<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class IndexRequestTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/IndexCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:index', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testIndexRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/IndexCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:index', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testIndexRequestGeneratorWithFullModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Cat/IndexCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:index', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
