<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\FileAppenders;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class LangTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldAppend(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        $this->artisan('admin:generate:lang', $arguments);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorShouldBeIdempotent(): void
    {
        $filePath = $this->app->langPath('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
        ]);

        $contentAfterFirst = file_get_contents($filePath);

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
        ]);

        $contentAfterSecond = file_get_contents($filePath);

        self::assertSame($contentAfterFirst, $contentAfterSecond);
    }

    public function testGeneratorShouldReplaceExistingKeyAfterUserEdit(): void
    {
        $filePath = $this->app->langPath('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
        ]);

        $originalContent = file_get_contents($filePath);

        file_put_contents(
            $filePath,
            str_replace("'Title'", "'Nadpis'", $originalContent),
        );

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
        ]);

        self::assertSame($originalContent, file_get_contents($filePath));
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'categories with belongs-to-many posts' => [
            'arguments' => ['table_name' => 'categories', '--belongs-to-many' => 'posts'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'categories with with-export' => [
            'arguments' => ['table_name' => 'categories', '--with-export' => true],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'categories with media gallery' => [
            'arguments' => ['table_name' => 'categories', '--media' => ['gallery:image:public:5000']],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'categories with locale nl' => [
            'arguments' => ['table_name' => 'categories', '--locale' => 'nl'],
            'expectedFilePath' => 'lang/nl/admin.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'posts with model-name App\\Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'App\\Feed\\Article'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'posts with belongs-to-many categories' => [
            'arguments' => ['table_name' => 'posts', '--belongs-to-many' => 'categories'],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'posts with with-export' => [
            'arguments' => ['table_name' => 'posts', '--with-export' => true],
            'expectedFilePath' => 'lang/en/admin.php',
        ];

        yield 'posts with locale nl' => [
            'arguments' => ['table_name' => 'posts', '--locale' => 'nl'],
            'expectedFilePath' => 'lang/nl/admin.php',
        ];
    }
}
