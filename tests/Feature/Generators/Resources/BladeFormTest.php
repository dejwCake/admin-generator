<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class BladeFormTest extends TestCase
{
    public function testBladeFormGeneratorShouldGenerateView(): void
    {
        $path = $this->app->resourcePath('views/admin/category/form.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeFormGeneratorWithModelNameShouldGenerateView(): void
    {
        $path = $this->app->resourcePath('views/admin/billing/categ-ory/form.blade.php');

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
        $path = $this->app->resourcePath('views/admin/categ-ory/form.blade.php');

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
        $path = $this->app->resourcePath('views/admin/profile/edit-password.blade.php');

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
        $path = $this->app->resourcePath('views/admin/category/form.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-form', [
            'table_name' => 'categories',
            '--route' => 'admin/categ-ories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
