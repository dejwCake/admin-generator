<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Appenders;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RoutesTest extends TestCase
{
    use DatabaseMigrations;

    public function testRoutesGeneratorShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithModelNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithFullModelNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Category',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithControllerNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--controller-name' => 'Billing\\CategOryController',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithFullControllerNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--controller-name' => 'App\\Http\\Billing\\CategOryController',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithExportShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithoutBulkShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }
}
