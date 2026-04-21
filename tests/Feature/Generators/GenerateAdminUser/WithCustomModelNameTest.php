<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateAdminUser;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class WithCustomModelNameTest extends TestCase
{
    public function testAllFilesShouldBeGeneratedWithCustomModel(): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/Auth/UsersController.php');
        $indexRequestPath = base_path('app/Http/Requests/Admin/User/IndexUser.php');
        $storeRequestPath = base_path('app/Http/Requests/Admin/User/StoreUser.php');
        $updateRequestPath = base_path('app/Http/Requests/Admin/User/UpdateUser.php');
        $destroyRequestPath = base_path('app/Http/Requests/Admin/User/DestroyUser.php');
        $bulkDestroyRequestPath = base_path('app/Http/Requests/Admin/User/BulkDestroyUser.php');
        $impersonalLoginRequestPath = base_path('app/Http/Requests/Admin/User/ImpersonalLoginUser.php');
        $exportPath = base_path('app/Exports/AdminUsersExport.php');
        $routesPath = base_path('routes/admin.php');
        $indexPath = resource_path('views/admin/user/index.blade.php');
        $listingVuePath = resource_path('js/admin/user/Listing.vue');
        $createPath = resource_path('views/admin/user/create.blade.php');
        $editPath = resource_path('views/admin/user/edit.blade.php');
        $formVuePath = resource_path('js/admin/user/Form.vue');
        $factoryPath = base_path('database/factories/UserFactory.php');
        $langPath = lang_path('en/admin.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($indexRequestPath);
        self::assertFileDoesNotExist($storeRequestPath);
        self::assertFileDoesNotExist($updateRequestPath);
        self::assertFileDoesNotExist($destroyRequestPath);
        self::assertFileDoesNotExist($bulkDestroyRequestPath);
        self::assertFileDoesNotExist($impersonalLoginRequestPath);
        self::assertFileDoesNotExist($exportPath);
        self::assertFileDoesNotExist($listingVuePath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);
        self::assertFileDoesNotExist($langPath);

        $this->artisan('admin:generate:admin-user', [
            '--controller-name' => 'Auth\\UsersController',
            '--model-name' => 'App\\User',
            '--with-export' => true,
        ]);

        self::assertFileExists($controllerPath);
        self::assertFileExists($indexRequestPath);
        self::assertFileExists($storeRequestPath);
        self::assertFileExists($updateRequestPath);
        self::assertFileExists($destroyRequestPath);
        self::assertFileExists($bulkDestroyRequestPath);
        self::assertFileExists($impersonalLoginRequestPath);
        self::assertFileExists($exportPath);
        self::assertFileExists($routesPath);
        self::assertFileExists($listingVuePath);
        self::assertFileExists($indexPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);
        self::assertFileExists($factoryPath);
        self::assertFileExists($langPath);

        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($indexRequestPath);
        self::assertMatchesFileSnapshot($storeRequestPath);
        self::assertMatchesFileSnapshot($updateRequestPath);
        self::assertMatchesFileSnapshot($destroyRequestPath);
        self::assertMatchesFileSnapshot($bulkDestroyRequestPath);
        self::assertMatchesFileSnapshot($impersonalLoginRequestPath);
        self::assertMatchesFileSnapshot($exportPath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($listingVuePath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
        self::assertMatchesFileSnapshot($factoryPath);
        self::assertMatchesFileSnapshot($langPath);
    }

    public function testAdminUserFactoryGeneratorShouldGenerateEverythingWithCustomModelName(): void
    {
        $filePath = base_path('database/factories/UserFactory.php');

        $this->artisan('admin:generate:admin-user', [
            '--model-name' => 'Auth\\User',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }
}
