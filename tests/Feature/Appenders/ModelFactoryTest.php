<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Appenders;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ModelFactoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testFactoryGeneratorShouldAutoGenerateEverythingFromTable(): void
    {
        $filePath = base_path('database/factories/ModelFactory.php');

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testYouCanSpecifyAModelName(): void
    {
        $filePath = base_path('database/factories/ModelFactory.php');

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testYouCanSpecifyAModelNameOutsideDefaultFolder(): void
    {
        $filePath = base_path('database/factories/ModelFactory.php');

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\MyCat',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }
}
