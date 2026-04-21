<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateAdminUserProfile;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class WithCustomControllerNameTest extends TestCase
{
    public function testProfileControllerNameCanBeNamespaced(): void
    {
        $filePathController = $this->app->basePath('app/Http/Controllers/Admin/Auth/ProfileController.php');
        $filePathRoute = $this->app->basePath('routes/admin.php');

        self::assertFileDoesNotExist($filePathController);

        $this->artisan('admin:generate:admin-user:profile', [
            '--controller-name' => 'Auth\\ProfileController',
        ]);

        self::assertFileExists($filePathController);
        self::assertMatchesFileSnapshot($filePathController);
        self::assertMatchesFileSnapshot($filePathRoute);
    }

    public function testProfileControllerNameCanBeOutsideDefaultDirectory(): void
    {
        $filePath = $this->app->basePath('app/Http/Controllers/Auth/ProfileController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:admin-user:profile', [
            '--controller-name' => 'App\\Http\\Controllers\\Auth\\ProfileController',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
