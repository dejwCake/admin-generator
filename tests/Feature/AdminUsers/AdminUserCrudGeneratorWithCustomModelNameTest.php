<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\AdminUsers;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminUserCrudGeneratorWithCustomModelNameTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testAllFilesShouldBeGeneratedWithCustomModel(): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/Auth/UsersController.php');
        $indexRequestPath = base_path('app/Http/Requests/Admin/User/IndexUser.php');
        $storeRequestPath = base_path('app/Http/Requests/Admin/User/StoreUser.php');
        $updateRequestPath = base_path('app/Http/Requests/Admin/User/UpdateUser.php');
        $destroyRequestPath = base_path('app/Http/Requests/Admin/User/DestroyUser.php');
        $impersonalLoginRequestPath = base_path('app/Http/Requests/Admin/User/ImpersonalLoginUser.php');
        $exportPath = base_path('app/Exports/AdminUsersExport.php');
        $routesPath = base_path('routes/admin.php');
        $indexPath = resource_path('views/admin/user/index.blade.php');
        $listingJsPath = resource_path('js/admin/user/Listing.js');
        $indexJsPath = resource_path('js/admin/user/index.js');
        $formPath = resource_path('views/admin/user/components/form-elements.blade.php');
        $createPath = resource_path('views/admin/user/create.blade.php');
        $editPath = resource_path('views/admin/user/edit.blade.php');
        $formJsPath = resource_path('js/admin/user/Form.js');
        $factoryPath = base_path('database/factories/UserFactory.php');
        $langPath = lang_path('en/admin.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($indexRequestPath);
        self::assertFileDoesNotExist($storeRequestPath);
        self::assertFileDoesNotExist($updateRequestPath);
        self::assertFileDoesNotExist($destroyRequestPath);
        self::assertFileDoesNotExist($impersonalLoginRequestPath);
        self::assertFileDoesNotExist($exportPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
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
        self::assertFileExists($impersonalLoginRequestPath);
        self::assertFileExists($exportPath);
        self::assertFileExists($routesPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($indexPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($formPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($factoryPath);
        self::assertFileExists($langPath);

        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($indexRequestPath);
        self::assertMatchesFileSnapshot($storeRequestPath);
        self::assertMatchesFileSnapshot($updateRequestPath);
        self::assertMatchesFileSnapshot($destroyRequestPath);
        self::assertMatchesFileSnapshot($impersonalLoginRequestPath);
        self::assertMatchesFileSnapshot($exportPath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
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
