<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ImpersonalLoginRequestTest extends TestCase
{
    use DatabaseMigrations;

    public function testImpersonalLoginRequestGeneratorShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Category/ImpersonalLoginCategory.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:impersonal-login', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testImpersonalLoginRequestGeneratorWithModelNameShouldGenerateClass(): void
    {
        $filePath = base_path('app/Http/Requests/Admin/Billing/Cat/ImpersonalLoginCat.php');

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:request:impersonal-login', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
