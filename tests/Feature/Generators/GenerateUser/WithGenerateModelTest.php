<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\GenerateUser;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class WithGenerateModelTest extends TestCase
{
    public function testUserModelNameShouldAutoGenerateFromTableNameIfRequired(): void
    {
        $filePath = $this->app->basePath('app/Models/User.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:user', [
            '--generate-model' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testUserModelNameShouldUseCustomNameIfRequired(): void
    {
        $filePath = $this->app->basePath('app/Models/Auth/User.php');

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
        $filePath = $this->app->basePath('app/Auth/User.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:user', [
            '--model-name' => 'App\\Auth\\User',
            '--generate-model' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
