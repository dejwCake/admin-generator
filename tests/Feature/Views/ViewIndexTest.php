<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Views;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewIndexTest extends TestCase
{
    use DatabaseMigrations;

    public function testViewIndexGeneratorShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/category/index.blade.php');
        $listingJsPath = resource_path('js/admin/category/Listing.js');
        $indexJsPath = resource_path('js/admin/category/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }

    public function testViewIndexGeneratorWithModelNameShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/billing/categ-ory/index.blade.php');
        $listingJsPath = resource_path('js/admin/billing-categ-ory/Listing.js');
        $indexJsPath = resource_path('js/admin/billing-categ-ory/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($indexJsPath);


        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }

    public function testViewIndexGeneratorWithFullModelNameShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/categ-ory/index.blade.php');
        $listingJsPath = resource_path('js/admin/categ-ory/Listing.js');
        $indexJsPath = resource_path('js/admin/categ-ory/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($indexJsPath);


        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }

    public function testViewIndexGeneratorWithExportShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/category/index.blade.php');
        $listingJsPath = resource_path('js/admin/category/Listing.js');
        $indexJsPath = resource_path('js/admin/category/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }

    public function testViewIndexGeneratorWithoutBulkShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/category/index.blade.php');
        $listingJsPath = resource_path('js/admin/category/Listing.js');
        $indexJsPath = resource_path('js/admin/category/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }
}
