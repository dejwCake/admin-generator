<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\FileAppenders;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class LangTest extends TestCase
{
    public function testLangGeneratorShouldAppend(): void
    {
        $filePath = $this->app->langPath('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithModelNameShouldAppend(): void
    {
        $filePath = $this->app->langPath('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithFullModelNameShouldAppend(): void
    {
        $filePath = $this->app->langPath('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithLocaleShouldAppend(): void
    {
        $filePath = $this->app->langPath('nl/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--locale' => 'nl',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithBelongsToManyShouldAppend(): void
    {
        $filePath = $this->app->langPath('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithExportShouldAppend(): void
    {
        $filePath = $this->app->langPath('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorShouldBeIdempotent(): void
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

    public function testLangGeneratorShouldReplaceExistingKeyAfterUserEdit(): void
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
}
