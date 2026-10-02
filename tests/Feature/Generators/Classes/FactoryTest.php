<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;

final class FactoryTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:factory', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('database/factories/CategoryFactory.php');

        $this->artisan('admin:generate:factory', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:factory', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public function testGeneratorShouldNameBooleanStatesInCamelCaps(): void
    {
        // The shared fixture only has single-word booleans, where the bug is invisible. A
        // snake_case column produced `up_event_enabled()`, and an `is_` one produced
        // `notIs_url_owner()` -- both breaking PSR1.Methods.CamelCapsMethodName.
        $this->app['db']->connection()->getSchemaBuilder()->create(
            'subscriptions',
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->boolean('up_event_enabled')->default(true);
                $table->boolean('is_url_owner')->default(false);
            },
        );

        $this->artisan('admin:generate:factory', ['table_name' => 'subscriptions']);

        $contents = (string) file_get_contents(
            $this->app->basePath('database/factories/SubscriptionFactory.php'),
        );

        self::assertStringContainsString('public function upEventEnabled(): self', $contents);
        self::assertStringContainsString('public function notUpEventEnabled(): self', $contents);
        self::assertStringContainsString('public function isUrlOwner(): self', $contents);
        self::assertStringContainsString('public function isNotUrlOwner(): self', $contents);

        // The column keys themselves must stay untouched.
        self::assertStringContainsString("['up_event_enabled' => true]", $contents);
        self::assertStringContainsString("['is_url_owner' => false]", $contents);

        self::assertStringNotContainsString('function up_event_enabled', $contents);
        self::assertStringNotContainsString('notIs_url_owner', $contents);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'database/factories/CategoryFactory.php',
        ];

        yield 'categories with translatable text' => [
            'arguments' => ['table_name' => 'categories', '--translatable' => 'text'],
            'expectedFilePath' => 'database/factories/CategoryFactory.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'database/factories/Billing/CatFactory.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'database/factories/Billing/CatFactory.php',
        ];

        yield 'categories with model-with-full-namespace App\\Billing\\Category' => [
            'arguments' => [
                'table_name' => 'categories',
                '--model-with-full-namespace' => 'App\\Billing\\Category',
            ],
            'expectedFilePath' => 'database/factories/Billing/CategoryFactory.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'database/factories/PostFactory.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'database/factories/Feed/ArticleFactory.php',
        ];

        yield 'posts with model-with-full-namespace App\\Feed\\Post' => [
            'arguments' => [
                'table_name' => 'posts',
                '--model-with-full-namespace' => 'App\\Feed\\Post',
            ],
            'expectedFilePath' => 'database/factories/Feed/PostFactory.php',
        ];

        yield 'admin-user with vendor namespace' => [
            'arguments' => [
                'table_name' => 'admin_users',
                '--model-with-full-namespace' => 'Brackets\\AdminAuth\\Models\\AdminUser',
            ],
            'expectedFilePath' => 'database/factories/Brackets/AdminAuth/Models/AdminUserFactory.php',
        ];
    }
}
