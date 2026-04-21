<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\FileAppenders;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class RoutesTest extends TestCase
{
    public function testRoutesGeneratorShouldAppend(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithModelNameShouldAppend(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithFullModelNameShouldAppend(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Category',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithControllerNameShouldAppend(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--controller-name' => 'Billing\\CategOryController',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithFullControllerNameShouldAppend(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--controller-name' => 'App\\Http\\Billing\\CategOryController',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithExportShouldAppend(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithoutBulkShouldAppend(): void
    {
        $filePath = $this->app->basePath('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorShouldBeIdempotent(): void
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

    public function testRoutesGeneratorShouldReplaceExistingBlock(): void
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

    public function testRoutesGeneratorShouldAddMultipleResources(): void
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
}
