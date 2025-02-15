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
        $storePath = base_path('app/Http/Requests/Admin/Category/StoreCategory.php');
        $updatePath = base_path('app/Http/Requests/Admin/Category/UpdateCategory.php');
        $destroyPath = base_path('app/Http/Requests/Admin/Category/DestroyCategory.php');
        $bulkDestroyPath = base_path('app/Http/Requests/Admin/Category/BulkDestroyCategory.php');
        $exportPath = base_path('app/Exports/CategoriesExport.php');
        $routesPath = base_path('routes/web.php');
        $indexPath = resource_path('views/admin/category/index.blade.php');
        $listingJsPath = resource_path('js/admin/category/Listing.js');
        $elementsPath = resource_path('views/admin/category/components/form-elements.blade.php');
        $createPath = resource_path('views/admin/category/create.blade.php');
        $editPath = resource_path('views/admin/category/edit.blade.php');
        $formJsPath = resource_path('js/admin/category/Form.js');
        $factoryPath = base_path('database/factories/ModelFactory.php');
        $indexJsPath = resource_path('js/admin/category/index.js');
        $langPath = resource_path('lang/en/admin.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($indexRequestPath);
        self::assertFileDoesNotExist($storePath);
        self::assertFileDoesNotExist($updatePath);
        self::assertFileDoesNotExist($destroyPath);
        self::assertFileDoesNotExist($bulkDestroyPath);
        self::assertFileDoesNotExist($exportPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($elementsPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($modelPath);
        self::assertFileDoesNotExist($routesPath);
        self::assertFileDoesNotExist($factoryPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($langPath);

        $this->artisan('admin:generate', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertFileExists($controllerPath);
        self::assertFileExists($indexRequestPath);
        self::assertFileExists($storePath);
        self::assertFileExists($updatePath);
        self::assertFileExists($destroyPath);
        self::assertFileExists($bulkDestroyPath);
        self::assertFileExists($exportPath);
        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($elementsPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($modelPath);
        self::assertFileExists($routesPath);
        self::assertFileExists($factoryPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($langPath);

        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($indexRequestPath);
        self::assertMatchesFileSnapshot($storePath);
        self::assertMatchesFileSnapshot($updatePath);
        self::assertMatchesFileSnapshot($destroyPath);
        self::assertMatchesFileSnapshot($bulkDestroyPath);
        self::assertMatchesFileSnapshot($exportPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($elementsPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($modelPath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($factoryPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($langPath);
    }
}
