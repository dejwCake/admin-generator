<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Profile;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProfileGeneratorWithCustomControllerNameTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testProfileControllerNameCanBeNamespaced(): void
    {
        $filePathController = base_path('app/Http/Controllers/Admin/Auth/ProfileController.php');
        $filePathRoute = base_path('routes/web.php');

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
        $filePath = base_path('app/Http/Controllers/Auth/ProfileController.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:admin-user:profile', [
            '--controller-name' => 'App\\Http\\Controllers\\Auth\\ProfileController',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
