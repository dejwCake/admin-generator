<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Users;

use Brackets\AdminGenerator\Tests\UserTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserCrudGeneratorWithGenerateModelTest extends UserTestCase
{
    use DatabaseMigrations;

    public function testUserModelNameShouldAutoGenerateFromTableNameIfRequired(): void
    {
        $filePath = base_path('app/Models/User.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:user', [
            '--generate-model' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testUserModelNameShouldUseCustomNameIfRequired(): void
    {
        $filePath = base_path('app/Models/Auth/User.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:user', [
            '--model-name' => 'Auth\\User',
            '--generate-model' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testUserModelNameShouldUseCustomNameOutsideDefaultFolderIfRequired(): void
    {
        $filePath = base_path('app/Auth/User.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:user', [
            '--model-name' => 'App\\Auth\\User',
            '--generate-model' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
