<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateUser;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class WithCustomModelNameTest extends TestCase
{
    public function testAllFilesShouldBeGeneratedWithCustomModel(): void
    {
        $controllerPath = $this->app->basePath('app/Http/Controllers/Admin/Auth/UsersController.php');
        $storeRequestPath = $this->app->basePath('app/Http/Requests/Admin/User/StoreUser.php');
        $updateRequestPath = $this->app->basePath('app/Http/Requests/Admin/User/UpdateUser.php');
        $routesPath = $this->app->basePath('routes/admin.php');
        $indexPath = $this->app->resourcePath('views/admin/user/index.blade.php');
        $listingVuePath = $this->app->resourcePath('js/admin/user/Listing.vue');
        $createPath = $this->app->resourcePath('views/admin/user/create.blade.php');
        $editPath = $this->app->resourcePath('views/admin/user/edit.blade.php');
        $formVuePath = $this->app->resourcePath('js/admin/user/Form.vue');
        $factoryPath = $this->app->basePath('database/factories/UserFactory.php');

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
        $filePath = $this->app->basePath('database/factories/UserFactory.php');

        $this->artisan('admin:generate:user', [
            '--model-name' => 'Auth\\User',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }
}
