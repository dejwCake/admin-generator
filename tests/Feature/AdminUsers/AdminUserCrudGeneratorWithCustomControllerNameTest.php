<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\AdminUsers;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminUserCrudGeneratorWithCustomControllerNameTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testAdminUserControllerNameCanBeNamespaced(): void
    {
        $filePathController = base_path('app/Http/Controllers/Admin/Auth/AdminUsersController.php');
        $filePathRoutes = base_path('routes/admin.php');

        self::assertFileDoesNotExist($filePathController);

        $this->artisan('admin:generate:admin-user', [
            '--controller-name' => 'Auth\\AdminUsersController',
        ]);

        self::assertFileExists($filePathController);

        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoutes);
    }

    public function testAdminUserControllerNameCanBeOutsideDefaultDirectory(): void
    {
        $filePathController = base_path('app/Http/Controllers/Auth/AdminUsersController.php');
        $filePathRoutes = base_path('routes/admin.php');

        self::assertFileDoesNotExist($filePathController);

        $this->artisan('admin:generate:admin-user', [
            '--controller-name' => 'App\\Http\\Controllers\\Auth\\AdminUsersController',
        ]);

        self::assertFileExists($filePathController);

        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoutes);
    }
}
