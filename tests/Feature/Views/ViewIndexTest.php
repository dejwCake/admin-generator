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
        $listingVuePath = resource_path('js/admin/category/Listing.vue');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
    }

    public function testViewIndexGeneratorWithModelNameShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/billing/categ-ory/index.blade.php');
        $listingVuePath = resource_path('js/admin/billing-categ-ory/Listing.vue');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
    }

    public function testViewIndexGeneratorWithFullModelNameShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/categ-ory/index.blade.php');
        $listingVuePath = resource_path('js/admin/categ-ory/Listing.vue');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
    }

    public function testViewIndexGeneratorWithExportShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/category/index.blade.php');
        $listingVuePath = resource_path('js/admin/category/Listing.vue');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
    }

    public function testViewIndexGeneratorWithoutBulkShouldGenerateViews(): void
    {
        $indexPath = resource_path('views/admin/category/index.blade.php');
        $listingVuePath = resource_path('js/admin/category/Listing.vue');

        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);

        $this->artisan('admin:generate:index', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
    }
}
