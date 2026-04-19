<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\FileAppenders;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RoutesTest extends TestCase
{
    use DatabaseMigrations;

    public function testRoutesGeneratorShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithModelNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithFullModelNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Category',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithControllerNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--controller-name' => 'Billing\\CategOryController',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithFullControllerNameShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--controller-name' => 'App\\Http\\Billing\\CategOryController',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithExportShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorWithoutBulkShouldAppend(): void
    {
        $filePath = base_path('routes/admin.php');

        $this->artisan('admin:generate:routes', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testRoutesGeneratorShouldBeIdempotent(): void
    {
        $filePath = base_path('routes/admin.php');

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
        $filePath = base_path('routes/admin.php');

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
        $filePath = base_path('routes/admin.php');

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
