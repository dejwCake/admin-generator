<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateUser;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class WithCustomModelNameTest extends TestCase
{
    public function testAllFilesShouldBeGeneratedWithCustomModel(): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/Auth/UsersController.php');
        $storeRequestPath = base_path('app/Http/Requests/Admin/User/StoreUser.php');
        $updateRequestPath = base_path('app/Http/Requests/Admin/User/UpdateUser.php');
        $routesPath = base_path('routes/admin.php');
        $indexPath = resource_path('views/admin/user/index.blade.php');
        $listingVuePath = resource_path('js/admin/user/Listing.vue');
        $createPath = resource_path('views/admin/user/create.blade.php');
        $editPath = resource_path('views/admin/user/edit.blade.php');
        $formVuePath = resource_path('js/admin/user/Form.vue');
        $factoryPath = base_path('database/factories/UserFactory.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($storeRequestPath);
        self::assertFileDoesNotExist($updateRequestPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);


        $this->artisan('admin:generate:user', [
            '--controller-name' => 'Auth\\UsersController',
            '--model-name' => 'App\\User',
        ]);

        self::assertFileExists($controllerPath);
        self::assertFileExists($storeRequestPath);
        self::assertFileExists($updateRequestPath);
        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);

        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($storeRequestPath);
        self::assertMatchesFileSnapshot($updateRequestPath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
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
