<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Appenders;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LangTest extends TestCase
{
    use DatabaseMigrations;

    public function testLangGeneratorShouldAppend(): void
    {
        $filePath = lang_path('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithModelNameShouldAppend(): void
    {
        $filePath = lang_path('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithFullModelNameShouldAppend(): void
    {
        $filePath = lang_path('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithLocaleShouldAppend(): void
    {
        $filePath = lang_path('nl/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--locale' => 'nl',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithBelongsToManyShouldAppend(): void
    {
        $filePath = lang_path('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testLangGeneratorWithExportShouldAppend(): void
    {
        $filePath = lang_path('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--with-export' => true,
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }
}
