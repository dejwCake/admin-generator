<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TrashControllerTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:trash-controller', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('app/Http/Controllers/Admin/CategoriesTrashController.php');

        $this->artisan('admin:generate:trash-controller', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:trash-controller', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public function testGeneratorSkipsTheClassWithoutDeleteAndBulkDelete(): void
    {
        $this->artisan('admin:generate:trash-controller', ['table_name' => 'categories', '--only' => 'create'])
            ->expectsOutput('Skipping the trash controller: neither delete nor bulk-delete is generated.');

        self::assertFileDoesNotExist($this->app->basePath('app/Http/Controllers/Admin/CategoriesTrashController.php'));
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesTrashController.php',
        ];

        yield 'categories only delete' => [
            'arguments' => ['table_name' => 'categories', '--only' => 'delete'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesTrashController.php',
        ];

        yield 'categories only bulk-delete' => [
            'arguments' => ['table_name' => 'categories', '--only' => 'bulk-delete'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesTrashController.php',
        ];

        yield 'categories with class_name Billing\\MyNameTrashController' => [
            'arguments' => ['table_name' => 'categories', 'class_name' => 'Billing\\MyNameTrashController'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/Billing/MyNameTrashController.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'app/Http/Controllers/Admin/CategoriesTrashController.php',
        ];
    }
}
