<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Views;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewFullFormTest extends TestCase
{
    use DatabaseMigrations;

    public function testViewFullFormGeneratorShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/category/form.blade.php');
        $formJsPath = resource_path('js/admin/category/Form.js');
        $indexJsPath = resource_path('js/admin/category/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
    }

    public function testViewFullFormGeneratorWithModelNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/billing/categ-ory/form.blade.php');
        $formJsPath = resource_path('js/admin/billing-categ-ory/Form.js');
        $indexJsPath = resource_path('js/admin/billing-categ-ory/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
    }

    public function testViewFullFormGeneratorWithFullModelNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/categ-ory/form.blade.php');
        $formJsPath = resource_path('js/admin/categ-ory/Form.js');
        $indexJsPath = resource_path('js/admin/categ-ory/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
    }

    public function testViewFormGeneratorWithFileNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/profile/edit-password.blade.php');
        $formJsPath = resource_path('js/admin/profile-edit-password/Form.js');
        $indexJsPath = resource_path('js/admin/profile-edit-password/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--file-name' => 'profile/edit-password',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
    }

    public function testViewFullFormGeneratorWithRouteShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/category/form.blade.php');
        $formJsPath = resource_path('js/admin/category/Form.js');
        $indexJsPath = resource_path('js/admin/category/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--route' => 'admin/categ-ories',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
    }
}
