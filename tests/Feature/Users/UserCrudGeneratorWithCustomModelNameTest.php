<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Users;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserCrudGeneratorWithCustomModelNameTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testAllFilesShouldBeGeneratedWithCustomModel(): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/Auth/UsersController.php');
        $storeRequestPath = base_path('app/Http/Requests/Admin/User/StoreUser.php');
        $updateRequestPath = base_path('app/Http/Requests/Admin/User/UpdateUser.php');
        $routesPath = base_path('routes/admin.php');
        $indexPath = resource_path('views/admin/user/index.blade.php');
        $indexJsPath = resource_path('js/admin/user/Listing.js');
        $formPath = resource_path('views/admin/user/components/form-elements.blade.php');
        $createPath = resource_path('views/admin/user/create.blade.php');
        $editPath = resource_path('views/admin/user/edit.blade.php');
        $formJsPath = resource_path('js/admin/user/Form.js');
        $factoryPath = base_path('database/factories/UserFactory.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($storeRequestPath);
        self::assertFileDoesNotExist($updateRequestPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);


        $this->artisan('admin:generate:user', [
            '--controller-name' => 'Auth\\UsersController',
            '--model-name' => 'App\\User',
        ]);

        self::assertFileExists($controllerPath);
        self::assertFileExists($storeRequestPath);
        self::assertFileExists($updateRequestPath);
        self::assertFileExists($indexPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($formPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($storeRequestPath);
        self::assertMatchesFileSnapshot($updateRequestPath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($factoryPath);
    }

    public function testUserFactoryGeneratorShouldGenerateEverythingWithCustomModelName(): void
    {
        $filePath = base_path('database/factories/UserFactory.php');

        $this->artisan('admin:generate:user', [
            '--model-name' => 'Auth\\User',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }
}
