<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateUser;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class WithCustomControllerNameTest extends TestCase
{
    public function testUserControllerNameCanBeNamespaced(): void
    {
        $filePathController = $this->app->basePath('app/Http/Controllers/Admin/Auth/UsersController.php');
        $filePathRoutes = $this->app->basePath('routes/admin.php');

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
        $filePath = $this->app->basePath('app/Http/Controllers/Auth/UsersController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:user', [
            '--controller-name' => 'App\\Http\\Controllers\\Auth\\UsersController',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
