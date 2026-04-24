<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\FileAppenders;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RoutesTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldAppend(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        $this->artisan('admin:generate:routes', $arguments);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorShouldBeIdempotent(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
        ]);
        $contentAfterFirst = file_get_contents($filePath);

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
        ]);
        $contentAfterSecond = file_get_contents($filePath);

        self::assertSame($contentAfterFirst, $contentAfterSecond);
    }

    public function testGeneratorShouldReplaceExistingBlock(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
        ]);

        // Re-generate with export — should replace, not duplicate
        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);
        $content = file_get_contents($filePath);

        self::assertStringContainsString("'export'", $content);
        self::assertSame(1, substr_count($content, '/* Auto-generated categories routes */'));
        self::assertSame(1, substr_count($content, '/* End of categories routes */'));
    }

    public function testGeneratorShouldAddMultipleResources(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
        ]);

        $this->artisan('admin:generate:routes', [
            'table_name' => 'posts',
        ]);
        $content = file_get_contents($filePath);

        self::assertStringContainsString('/* Auto-generated categories routes */', $content);
        self::assertStringContainsString('/* Auto-generated posts routes */', $content);
        self::assertSame(
            1,
            substr_count($content, "//-- Do not delete me :) I'm used for auto-generation admin routes uses --"),
        );
        self::assertSame(
            1,
            substr_count($content, "//-- Do not delete me :) I'm used for auto-generation admin routes --"),
        );
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with controller-name Billing\\CategoryController' => [
            'arguments' => [
                'table_name' => 'categories',
                '--controller-name' => 'Billing\\CategoryController',
            ],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with controller-name App\\Http\\Billing\\CategoryController' => [
            'arguments' => [
                'table_name' => 'categories',
                '--controller-name' => 'App\\Http\\Billing\\CategoryController',
            ],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with with-export' => [
            'arguments' => ['table_name' => 'categories', '--with-export' => true],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories without bulk' => [
            'arguments' => ['table_name' => 'categories', '--without-bulk' => true],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with template admin-user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'admin-user'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with template user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'user'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'categories with resource custom-resource' => [
            'arguments' => ['table_name' => 'categories', '--resource' => 'custom-resource'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'posts with model-name App\\Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'App\\Feed\\Article'],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'posts with controller-name Feed\\PostController' => [
            'arguments' => [
                'table_name' => 'posts',
                '--controller-name' => 'Feed\\PostController',
            ],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'posts with controller-name App\\Http\\Feed\\PostController' => [
            'arguments' => [
                'table_name' => 'posts',
                '--controller-name' => 'App\\Http\\Feed\\PostController',
            ],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'posts with with-export' => [
            'arguments' => ['table_name' => 'posts', '--with-export' => true],
            'expectedFilePath' => 'routes/admin.php',
        ];

        yield 'posts without bulk' => [
            'arguments' => ['table_name' => 'posts', '--without-bulk' => true],
            'expectedFilePath' => 'routes/admin.php',
        ];
    }
}
