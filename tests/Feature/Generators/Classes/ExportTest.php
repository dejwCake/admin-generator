<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class ExportTest extends TestCase
{
    public function testExportGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('app/Exports/CategoriesExport.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:export', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testExportGeneratorWithModelWithFullNamespaceShouldGenerateClass(): void
    {
        $filePath = base_path('app/Exports/CategoriesExport.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:export', [
            'table_name' => 'categories',
            '--model-with-full-namespace' => 'App\\Billing\\Category',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
