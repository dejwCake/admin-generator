<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FactoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testFactoryGenerationShouldGenerateAFactoryName(): void
    {
        $filePath = base_path('database/factories/CategoryFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testIsGeneratedCorrectNameForCustomModelName(): void
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

    public function testIsGeneratedCorrectNameForCustomModelNameOutsideDefaultFolder(): void
    {
        $filePath = base_path('database/factories/MyCatFactory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\MyCat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
