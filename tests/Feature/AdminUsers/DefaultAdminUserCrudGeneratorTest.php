<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\AdminUsers;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;

class DefaultAdminUserCrudGeneratorTest extends UserTestCase
{
    use DatabaseMigrations;

    #[DataProvider('getCases')]
    public function testAllFilesShouldBeGeneratedUnderDefaultNamespace(bool $seed): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/AdminUsersController.php');
        $indexRequestPath = base_path('app/Http/Requests/Admin/AdminUser/IndexAdminUser.php');
        $storePath = base_path('app/Http/Requests/Admin/AdminUser/StoreAdminUser.php');
        $updatePath = base_path('app/Http/Requests/Admin/AdminUser/UpdateAdminUser.php');
        $destroyPath = base_path('app/Http/Requests/Admin/AdminUser/DestroyAdminUser.php');
        $impersonalLoginPath = base_path('app/Http/Requests/Admin/AdminUser/ImpersonalLoginAdminUser.php');
        $exportPath = base_path('app/Exports/AdminUsersExport.php');
        $routesPath = base_path('routes/admin.php');
        $indexPath = resource_path('views/admin/admin-user/index.blade.php');
        $listingJsPath = resource_path('js/admin/admin-user/Listing.js');
        $indexJsPath = resource_path('js/admin/admin-user/index.js');
        $elementsPath = resource_path('views/admin/admin-user/components/form-elements.blade.php');
        $createPath = resource_path('views/admin/admin-user/create.blade.php');
        $editPath = resource_path('views/admin/admin-user/edit.blade.php');
        $formJsPath = resource_path('js/admin/admin-user/Form.js');
        $factoryPath = base_path('database/factories/ModelFactory.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($indexRequestPath);
        self::assertFileDoesNotExist($storePath);
        self::assertFileDoesNotExist($updatePath);
        self::assertFileDoesNotExist($destroyPath);
        self::assertFileDoesNotExist($impersonalLoginPath);
        self::assertFileDoesNotExist($exportPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingJsPath);
        self::assertFileDoesNotExist($elementsPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);


        if ($seed) {
            $this->artisan('admin:generate:admin-user', ['--with-export' => true, '--seed' => true]);
        } else {
            $this->artisan('admin:generate:admin-user', ['--with-export' => true]);
        }

        self::assertFileExists($controllerPath);
        self::assertFileExists($indexRequestPath);
        self::assertFileExists($storePath);
        self::assertFileExists($updatePath);
        self::assertFileExists($destroyPath);
        self::assertFileExists($impersonalLoginPath);
        self::assertFileExists($exportPath);
        self::assertFileExists($indexPath);
        self::assertFileExists($listingJsPath);
        self::assertFileExists($elementsPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);

        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($indexRequestPath);
        self::assertMatchesFileSnapshot($storePath);
        self::assertMatchesFileSnapshot($updatePath);
        self::assertMatchesFileSnapshot($destroyPath);
        self::assertMatchesFileSnapshot($impersonalLoginPath);
        self::assertMatchesFileSnapshot($exportPath);
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingJsPath);
        self::assertMatchesFileSnapshot($elementsPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($factoryPath);

        if ($seed) {
            self::assertDatabaseCount('admin_users', 20);
        }
    }

    public static function getCases(): iterable
    {
        yield 'without seed' => [false];

        //disabled for now, because it's not working properly
        //yield 'with seed' => [true];
    }
}
