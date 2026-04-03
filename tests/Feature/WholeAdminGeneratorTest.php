<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class WholeAdminGeneratorTest extends TestCase
{
    use DatabaseMigrations;

    public function testWholeAdminGeneratorProducesAllTheFiles(): void
    {
        $modelPath = base_path('app/Models/Category.php');
        $controllerPath = base_path('app/Http/Controllers/Admin/CategoriesController.php');
        $indexRequestPath = base_path('app/Http/Requests/Admin/Category/IndexCategory.php');
        $storeRequestPath = base_path('app/Http/Requests/Admin/Category/StoreCategory.php');
        $updateRequestPath = base_path('app/Http/Requests/Admin/Category/UpdateCategory.php');
        $destroyRequestPath = base_path('app/Http/Requests/Admin/Category/DestroyCategory.php');
        $bulkDestroyRequestPath = base_path('app/Http/Requests/Admin/Category/BulkDestroyCategory.php');
        $exportPath = base_path('app/Exports/CategoriesExport.php');
        $routesPath = base_path('routes/admin.php');
        $indexPath = resource_path('views/admin/category/index.blade.php');
        $listingVuePath = resource_path('js/admin/category/Listing.vue');
        $createPath = resource_path('views/admin/category/create.blade.php');
        $editPath = resource_path('views/admin/category/edit.blade.php');
        $formVuePath = resource_path('js/admin/category/Form.vue');
        $factoryPath = base_path('database/factories/CategoryFactory.php');
        $langPath = lang_path('en/admin.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($indexRequestPath);
        self::assertFileDoesNotExist($storeRequestPath);
        self::assertFileDoesNotExist($updateRequestPath);
        self::assertFileDoesNotExist($destroyRequestPath);
        self::assertFileDoesNotExist($bulkDestroyRequestPath);
        self::assertFileDoesNotExist($exportPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);
        self::assertFileDoesNotExist($modelPath);
        self::assertFileDoesNotExist($routesPath);
        self::assertFileDoesNotExist($factoryPath);
        self::assertFileDoesNotExist($langPath);

        $this->artisan('admin:generate', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);


        self::assertFileExists($controllerPath);
        self::assertFileExists($indexRequestPath);
        self::assertFileExists($storeRequestPath);
        self::assertFileExists($updateRequestPath);
        self::assertFileExists($destroyRequestPath);
        self::assertFileExists($bulkDestroyRequestPath);
        self::assertFileExists($exportPath);
        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);
        self::assertFileExists($modelPath);
        self::assertFileExists($routesPath);
        self::assertFileExists($factoryPath);
        self::assertFileExists($langPath);

        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($indexRequestPath);
        self::assertMatchesFileSnapshot($storeRequestPath);
        self::assertMatchesFileSnapshot($updateRequestPath);
        self::assertMatchesFileSnapshot($destroyRequestPath);
        self::assertMatchesFileSnapshot($bulkDestroyRequestPath);
        self::assertMatchesFileSnapshot($exportPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
        self::assertMatchesFileSnapshot($modelPath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($factoryPath);
        self::assertMatchesFileSnapshot($langPath);
    }
}
