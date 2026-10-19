<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;

final class VueShowTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateComponent(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:vue-show', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteComponent(): void
    {
        $filePath = $this->app->basePath('resources/js/admin/category/Show.vue');

        $this->artisan('admin:generate:vue-show', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:vue-show', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public function testGeneratedComponentNeverRendersASecret(): void
    {
        $this->artisan('admin:generate:vue-show', ['table_name' => 'categories']);

        $contents = (string) file_get_contents($this->app->basePath('resources/js/admin/category/Show.vue'));

        self::assertStringNotContainsString('password', $contents);
        self::assertStringNotContainsString('remember_token', $contents);
        self::assertStringNotContainsString('two_factor', $contents);
    }

    /**
     * A detail view answers "what is this record", so unlike a form it keeps the id and the
     * timestamps.
     */
    public function testGeneratedComponentKeepsTheIdAndTimestamps(): void
    {
        $this->artisan('admin:generate:vue-show', ['table_name' => 'categories']);

        $contents = (string) file_get_contents($this->app->basePath('resources/js/admin/category/Show.vue'));

        self::assertStringContainsString('translations.columns.id', $contents);
        self::assertStringContainsString('translations.columns.created_at', $contents);
        self::assertStringContainsString('translations.columns.updated_at', $contents);
    }

    public function testBooleansRenderAsAReadOnlySwitch(): void
    {
        $this->artisan('admin:generate:vue-show', ['table_name' => 'categories']);

        $contents = (string) file_get_contents($this->app->basePath('resources/js/admin/category/Show.vue'));

        self::assertStringContainsString('<ReadOnlySwitch :modelValue="data.enabled"/>', $contents);
        self::assertStringContainsString("ReadOnlySwitch from '@craftable/components/ReadOnlySwitch.vue'", $contents);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'resources/js/admin/category/Show.vue',
        ];

        yield 'categories with translatable text' => [
            'arguments' => ['table_name' => 'categories', '--translatable' => 'text'],
            'expectedFilePath' => 'resources/js/admin/category/Show.vue',
        ];

        yield 'categories with model-name Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\CategOry'],
            'expectedFilePath' => 'resources/js/admin/billing-categ-ory/Show.vue',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'resources/js/admin/post/Show.vue',
        ];
    }
}
