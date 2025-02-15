<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Appenders;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LangTest extends TestCase
{
    use DatabaseMigrations;

    public function testAutoGeneratedLangAppend(): void
    {
        $filePath = lang_path('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }

    public function testNamespacedModelLangAppend(): void
    {
        $filePath = lang_path('en/admin.php');

        $this->artisan('admin:generate:lang', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertMatchesFileSnapshot($filePath);
    }
}
