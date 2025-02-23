<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FactoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testFactoryGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('database/factories/CategoryFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testFactoryGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('database/factories/CatFactory.php');

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
        $filePath = base_path('database/factories/CatFactory.php');

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
        $filePath = base_path('database/factories/CategoryFactory.php');

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
        $filePath = base_path('database/factories/CategoryFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--seed' => true,
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
