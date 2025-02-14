<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Users;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserCrudGeneratorWithCustomControllerNameTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testUserControllerNameCanBeNamespaced(): void
    {
        $filePathController = base_path('app/Http/Controllers/Admin/Auth/UsersController.php');
        $filePathRoutes = base_path('routes/web.php');

        self::assertFileDoesNotExist($filePathController);

        $this->artisan('admin:generate:user', [
            '--controller-name' => 'Auth\\UsersController',
        ]);

        self::assertFileExists($filePathController);
        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoutes);
    }

    public function testUserControllerNameCanBeOutsideDefaultDirectory(): void
    {
        $filePath = base_path('app/Http/Controllers/Auth/UsersController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:user', [
            '--controller-name' => 'App\\Http\\Controllers\\Auth\\UsersController',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
