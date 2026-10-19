<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;

final class BladeShowTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateView(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:blade-show', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteView(): void
    {
        $filePath = $this->app->basePath('resources/views/admin/category/show.blade.php');

        $this->artisan('admin:generate:blade-show', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:blade-show', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    /**
     * A detail view renders whatever it is given, so a secret must never reach it. `getForForm()`
     * keeps `password` because it feeds forms, where a password field belongs; the show view uses
     * `getForShow()` instead.
     */
    public function testGeneratedViewNeverRendersASecret(): void
    {
        $this->artisan('admin:generate:blade-show', ['table_name' => 'categories']);

        $contents = (string) file_get_contents(
            $this->app->basePath('resources/views/admin/category/show.blade.php'),
        );

        self::assertStringNotContainsString('password', $contents);
        self::assertStringNotContainsString('remember_token', $contents);
        self::assertStringNotContainsString('two_factor', $contents);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'resources/views/admin/category/show.blade.php',
        ];

        yield 'categories with translatable text' => [
            'arguments' => ['table_name' => 'categories', '--translatable' => 'text'],
            'expectedFilePath' => 'resources/views/admin/category/show.blade.php',
        ];

        yield 'categories with model-name Billing\\CategOry' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\CategOry'],
            'expectedFilePath' => 'resources/views/admin/billing/categ-ory/show.blade.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'resources/views/admin/post/show.blade.php',
        ];
    }
}
