<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class BladeIndexTest extends TestCase
{
    public function testBladeIndexGeneratorShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/index.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-index', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeIndexGeneratorWithModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/billing/categ-ory/index.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-index', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeIndexGeneratorWithFullModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/categ-ory/index.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-index', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeIndexGeneratorWithExportShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/index.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-index', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeIndexGeneratorWithoutBulkShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/index.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-index', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
