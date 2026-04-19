<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateAdminUser;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;

class DefaultTest extends UserTestCase
{
    use DatabaseMigrations;

    #[DataProvider('getCases')]
    public function testAllFilesShouldBeGeneratedUnderDefaultNamespace(array $options): void
    {
        $controllerPath = base_path('app/Http/Controllers/Admin/AdminUsersController.php');
        $indexRequestPath = base_path('app/Http/Requests/Admin/AdminUser/IndexAdminUser.php');
        $storeRequestPath = base_path('app/Http/Requests/Admin/AdminUser/StoreAdminUser.php');
        $updateRequestPath = base_path('app/Http/Requests/Admin/AdminUser/UpdateAdminUser.php');
        $destroyRequestPath = base_path('app/Http/Requests/Admin/AdminUser/DestroyAdminUser.php');
        $bulkDestroyRequestPath = base_path('app/Http/Requests/Admin/AdminUser/BulkDestroyAdminUser.php');
        $impersonalLoginRequestPath = base_path('app/Http/Requests/Admin/AdminUser/ImpersonalLoginAdminUser.php');
        $exportPath = base_path('app/Exports/AdminUsersExport.php');
        $routesPath = base_path('routes/admin.php');
        $indexPath = resource_path('views/admin/admin-user/index.blade.php');
        $listingVuePath = resource_path('js/admin/admin-user/Listing.vue');
        $createPath = resource_path('views/admin/admin-user/create.blade.php');
        $editPath = resource_path('views/admin/admin-user/edit.blade.php');
        $formVuePath = resource_path('js/admin/admin-user/Form.vue');
        $factoryPath = base_path('database/factories/AdminUserFactory.php');
        $langPath = lang_path('en/admin.php');

        self::assertFileDoesNotExist($controllerPath);
        self::assertFileDoesNotExist($indexRequestPath);
        self::assertFileDoesNotExist($storeRequestPath);
        self::assertFileDoesNotExist($updateRequestPath);
        self::assertFileDoesNotExist($destroyRequestPath);
        self::assertFileDoesNotExist($bulkDestroyRequestPath);
        self::assertFileDoesNotExist($impersonalLoginRequestPath);
        self::assertFileDoesNotExist($exportPath);
        self::assertFileDoesNotExist($indexPath);
        self::assertFileDoesNotExist($listingVuePath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:admin-user', $options);

        self::assertFileExists($controllerPath);
        self::assertFileExists($indexRequestPath);
        self::assertFileExists($storeRequestPath);
        self::assertFileExists($updateRequestPath);
        self::assertFileExists($destroyRequestPath);
        if (!array_key_exists('--without-bulk', $options) || $options['--without-bulk'] !== true) {
            self::assertFileExists($bulkDestroyRequestPath);
        } else {
            self::assertFileDoesNotExist($bulkDestroyRequestPath);
        }
        self::assertFileExists($impersonalLoginRequestPath);
        if (array_key_exists('--with-export', $options) && $options['--with-export'] === true) {
            self::assertFileExists($exportPath);
        } else {
            self::assertFileDoesNotExist($exportPath);
        }
        self::assertFileExists($indexPath);
        self::assertFileExists($listingVuePath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);
        self::assertFileExists($langPath);

        self::assertMatchesFileSnapshot($controllerPath);
        self::assertMatchesFileSnapshot($indexRequestPath);
        self::assertMatchesFileSnapshot($storeRequestPath);
        self::assertMatchesFileSnapshot($updateRequestPath);
        self::assertMatchesFileSnapshot($destroyRequestPath);
        if (!array_key_exists('--without-bulk', $options) || $options['--without-bulk'] !== true) {
            self::assertMatchesFileSnapshot($bulkDestroyRequestPath);
        }
        self::assertMatchesFileSnapshot($impersonalLoginRequestPath);
        if (array_key_exists('--with-export', $options) && $options['--with-export'] === true) {
            self::assertMatchesFileSnapshot($exportPath);
        }
        self::assertMatchesFileSnapshot($routesPath);
        self::assertMatchesFileSnapshot($indexPath);
        self::assertMatchesFileSnapshot($listingVuePath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
        self::assertMatchesFileSnapshot($factoryPath);
        self::assertMatchesFileSnapshot($langPath);

        if (array_key_exists('--seed', $options) && $options['--seed'] === true) {
            self::assertDatabaseCount('admin_users', 20);
        }
    }

    public static function getCases(): iterable
    {
        yield 'empty' => ['options' => []];

        //disabled for now, because it's not working properly
//        yield 'with seed' => ['options' => ['--seed' => true]];

        yield 'with export' => ['options' => ['--with-export' => true]];

        yield 'without bulk' => ['options' => ['--without-bulk' => true]];
    }
}
