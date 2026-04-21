<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class FactoryTest extends TestCase
{
    public function testFactoryGeneratorShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('database/factories/CategoryFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testFactoryGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('database/factories/CatFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testFactoryGeneratorWithFullModelNameShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('database/factories/CatFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testModelGeneratorWithModelWithFullNamespaceShouldGenerateClass(): void
    {
        $filePath = $this->app->basePath('database/factories/CategoryFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-with-full-namespace' => 'App\\Billing\\Category',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testFactoryGeneratorWithSeedShouldGenerateClass(): void
    {
        $this->markTestSkipped('This test is skipped as we do not generate model');
        $filePath = $this->app->basePath('database/factories/CategoryFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--seed' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
