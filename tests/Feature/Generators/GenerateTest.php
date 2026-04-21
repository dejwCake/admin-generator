<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class GenerateTest extends TestCase
{
    public function testWholeAdminGeneratorProducesAllTheFiles(): void
    {
        $modelPath = $this->app->basePath('app/Models/Category.php');
        $controllerPath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php');
        $indexRequestPath = $this->app->basePath('app/Http/Requests/Admin/Category/IndexCategory.php');
        $storeRequestPath = $this->app->basePath('app/Http/Requests/Admin/Category/StoreCategory.php');
        $updateRequestPath = $this->app->basePath('app/Http/Requests/Admin/Category/UpdateCategory.php');
        $destroyRequestPath = $this->app->basePath('app/Http/Requests/Admin/Category/DestroyCategory.php');
        $bulkDestroyRequestPath = $this->app->basePath('app/Http/Requests/Admin/Category/BulkDestroyCategory.php');
        $exportPath = $this->app->basePath('app/Exports/CategoriesExport.php');
        $routesPath = $this->app->basePath('routes/admin.php');
        $indexPath = $this->app->resourcePath('views/admin/category/index.blade.php');
        $listingVuePath = $this->app->resourcePath('js/admin/category/Listing.vue');
        $createPath = $this->app->resourcePath('views/admin/category/create.blade.php');
        $editPath = $this->app->resourcePath('views/admin/category/edit.blade.php');
        $formVuePath = $this->app->resourcePath('js/admin/category/Form.vue');
        $factoryPath = $this->app->basePath('database/factories/CategoryFactory.php');
        $langPath = $this->app->langPath('en/admin.php');

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
