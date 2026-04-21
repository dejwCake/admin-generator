<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateAdminUser;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class WithCustomControllerNameTest extends TestCase
{
    public function testAdminUserControllerNameCanBeNamespaced(): void
    {
        $filePathController = $this->app->basePath('app/Http/Controllers/Admin/Auth/AdminUsersController.php');
        $filePathRoutes = $this->app->basePath('routes/admin.php');

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
        $filePathController = $this->app->basePath('app/Http/Controllers/Auth/AdminUsersController.php');
        $filePathRoutes = $this->app->basePath('routes/admin.php');

        self::assertFileDoesNotExist($filePathController);

        $this->artisan('admin:generate:admin-user', [
            '--controller-name' => 'App\\Http\\Controllers\\Auth\\AdminUsersController',
        ]);

        self::assertFileExists($filePathController);

        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoutes);
    }
}
