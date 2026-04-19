<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BladeFormTest extends TestCase
{
    use DatabaseMigrations;

    public function testBladeFormGeneratorShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/form.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeFormGeneratorWithModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/billing/categ-ory/form.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeFormGeneratorWithFullModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/categ-ory/form.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeFormGeneratorWithFileNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/profile/edit-password.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
            '--file-name' => 'profile/edit-password',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeFormGeneratorWithRouteShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/form.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
            '--route' => 'admin/categ-ories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
