<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class IndexRequestTest extends TestCase
{
    public function testIndexRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Category/IndexCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:index', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testIndexRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Billing/Cat/IndexCat.php');

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
        $filePath = $this->app->basePath('app/Http/Requests/Admin/Cat/IndexCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:index', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
