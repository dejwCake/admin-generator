<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ModelTest extends TestCase
{
    use DatabaseMigrations;

    public function testModelNameShouldAutoGenerateFromTableName(): void
    {
        $filePath = base_path('app/Models/Category.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:model', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testYouCanPassCustomClassNameForTheModel(): void
    {
        $filePath = base_path('app/Models/Billing/Category.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:model', [
            'table_name' => 'categories',
            'class_name' => 'Billing\\Category',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testClassNameCanBeOutsideDefaultFolder(): void
    {
        $filePath = base_path('app/Billing/Category.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:model', [
            'table_name' => 'categories',
            'class_name' => 'App\\Billing\\Category',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
