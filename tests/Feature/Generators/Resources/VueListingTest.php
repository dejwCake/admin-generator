<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class VueListingTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateComponent(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:vue-listing', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteComponent(): void
    {
        $filePath = $this->app->basePath('resources/js/admin/category/Listing.vue');

        $this->artisan('admin:generate:vue-listing', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:vue-listing', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'resources/js/admin/category/Listing.vue',
        ];

        yield 'categories with model-name Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\CategOry'],
            'expectedFilePath' => 'resources/js/admin/billing-categ-ory/Listing.vue',
        ];

        yield 'categories with model-name App\\Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\CategOry'],
            'expectedFilePath' => 'resources/js/admin/categ-ory/Listing.vue',
        ];

        yield 'categories with with-export' => [
            'arguments' => ['table_name' => 'categories', '--with-export' => true],
            'expectedFilePath' => 'resources/js/admin/category/Listing.vue',
        ];

        yield 'categories without bulk' => [
            'arguments' => ['table_name' => 'categories', '--without-bulk' => true],
            'expectedFilePath' => 'resources/js/admin/category/Listing.vue',
        ];

        yield 'categories with template admin-user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'admin-user'],
            'expectedFilePath' => 'resources/js/admin/category/Listing.vue',
        ];

        yield 'categories with template user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'user'],
            'expectedFilePath' => 'resources/js/admin/category/Listing.vue',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'resources/js/admin/post/Listing.vue',
        ];

        yield 'posts with model-name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'Feed\\Article'],
            'expectedFilePath' => 'resources/js/admin/feed-article/Listing.vue',
        ];

        yield 'posts with model-name App\\Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', '--model-name' => 'App\\Feed\\Article'],
            'expectedFilePath' => 'resources/js/admin/article/Listing.vue',
        ];

        yield 'posts with with-export' => [
            'arguments' => ['table_name' => 'posts', '--with-export' => true],
            'expectedFilePath' => 'resources/js/admin/post/Listing.vue',
        ];

        yield 'posts without bulk' => [
            'arguments' => ['table_name' => 'posts', '--without-bulk' => true],
            'expectedFilePath' => 'resources/js/admin/post/Listing.vue',
        ];
    }
}
