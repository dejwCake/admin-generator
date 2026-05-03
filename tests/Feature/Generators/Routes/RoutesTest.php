<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Routes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RoutesTest extends TestCase
{
    /**
     * @param array<string, string|bool> $arguments
     */
    #[DataProvider('getCases')]
    public function testGeneratorWritesPerResourceFile(array $arguments, string $expectedFilePath): void
    {
        $this->artisan('admin:generate:routes', $arguments);

        self::assertMatchesFileSnapshot($this->app->basePath($expectedFilePath));
    }

    public function testGeneratorCreatesUmbrellaOnFirstRun(): void
    {
        $umbrellaPath = $this->app->basePath('routes/admin.php');

        self::assertFileDoesNotExist($umbrellaPath);

        $this->artisan('admin:generate:routes', ['table_name' => 'categories']);

        self::assertFileExists($umbrellaPath);
        self::assertMatchesFileSnapshot($umbrellaPath);
    }

    public function testUmbrellaIsNotRewrittenOnSubsequentRuns(): void
    {
        $umbrellaPath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', ['table_name' => 'categories']);
        $afterFirst = (string) file_get_contents($umbrellaPath);

        $this->artisan('admin:generate:routes', ['table_name' => 'posts']);
        $afterSecond = (string) file_get_contents($umbrellaPath);

        self::assertSame($afterFirst, $afterSecond);
    }

    public function testReGeneratingSameResourceOverwritesPerResourceFile(): void
    {
        $resourceFile = $this->app->basePath('routes/admin/categories.php');

        $this->artisan('admin:generate:routes', ['table_name' => 'categories']);
        $afterFirst = (string) file_get_contents($resourceFile);
        self::assertStringNotContainsString("'export'", $afterFirst);

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);
        $afterSecond = (string) file_get_contents($resourceFile);

        self::assertStringContainsString("'export'", $afterSecond);
    }

    public function testDifferentResourcesProduceDifferentFiles(): void
    {
        $this->artisan('admin:generate:routes', ['table_name' => 'categories']);
        $this->artisan('admin:generate:routes', ['table_name' => 'posts']);

        self::assertFileExists($this->app->basePath('routes/admin/categories.php'));
        self::assertFileExists($this->app->basePath('routes/admin/posts.php'));
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'routes/admin/categories.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'routes/admin/billing-cats.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'routes/admin/cats.php',
        ];

        yield 'categories with controller-name Billing\\CategoryController' => [
            'arguments' => [
                'table_name' => 'categories',
                '--controller-name' => 'Billing\\CategoryController',
            ],
            'expectedFilePath' => 'routes/admin/categories.php',
        ];

        yield 'categories with controller-name App\\Http\\Billing\\CategoryController' => [
            'arguments' => [
                'table_name' => 'categories',
                '--controller-name' => 'App\\Http\\Billing\\CategoryController',
            ],
            'expectedFilePath' => 'routes/admin/categories.php',
        ];

        yield 'categories with with-export' => [
            'arguments' => ['table_name' => 'categories', '--with-export' => true],
            'expectedFilePath' => 'routes/admin/categories.php',
        ];

        yield 'categories without bulk' => [
            'arguments' => ['table_name' => 'categories', '--without-bulk' => true],
            'expectedFilePath' => 'routes/admin/categories.php',
        ];

        yield 'categories with template admin-user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'admin-user'],
            'expectedFilePath' => 'routes/admin/categories.php',
        ];

        yield 'categories with template user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'user'],
            'expectedFilePath' => 'routes/admin/categories.php',
        ];

        yield 'categories with resource custom-resource' => [
            'arguments' => ['table_name' => 'categories', '--resource' => 'custom-resource'],
            'expectedFilePath' => 'routes/admin/custom-resource.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'routes/admin/posts.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'routes/admin/feed-articles.php',
        ];

        yield 'posts with model-name App\\Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'App\\Feed\\Article'],
            'expectedFilePath' => 'routes/admin/articles.php',
        ];

        yield 'posts with controller-name Feed\\PostController' => [
            'arguments' => [
                'table_name' => 'posts',
                '--controller-name' => 'Feed\\PostController',
            ],
            'expectedFilePath' => 'routes/admin/posts.php',
        ];

        yield 'posts with controller-name App\\Http\\Feed\\PostController' => [
            'arguments' => [
                'table_name' => 'posts',
                '--controller-name' => 'App\\Http\\Feed\\PostController',
            ],
            'expectedFilePath' => 'routes/admin/posts.php',
        ];

        yield 'posts with with-export' => [
            'arguments' => ['table_name' => 'posts', '--with-export' => true],
            'expectedFilePath' => 'routes/admin/posts.php',
        ];

        yield 'posts without bulk' => [
            'arguments' => ['table_name' => 'posts', '--without-bulk' => true],
            'expectedFilePath' => 'routes/admin/posts.php',
        ];
    }
}
