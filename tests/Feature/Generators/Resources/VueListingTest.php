<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class VueListingTest extends TestCase
{
    public function testVueListingGeneratorShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/category/Listing.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-listing', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueListingGeneratorWithModelNameShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/billing-categ-ory/Listing.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-listing', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueListingGeneratorWithFullModelNameShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/categ-ory/Listing.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-listing', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueListingGeneratorWithExportShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/category/Listing.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-listing', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueListingGeneratorWithoutBulkShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/category/Listing.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-listing', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
