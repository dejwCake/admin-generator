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
        $formVuePath = resource_path('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }

    public function testViewFullFormGeneratorWithModelNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/billing/categ-ory/form.blade.php');
        $formVuePath = resource_path('js/admin/billing-categ-ory/Form.vue');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }

    public function testViewFullFormGeneratorWithFullModelNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/categ-ory/form.blade.php');
        $formVuePath = resource_path('js/admin/categ-ory/Form.vue');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }

    public function testViewFormGeneratorWithFileNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/profile/edit-password.blade.php');
        $formVuePath = resource_path('js/admin/profile-edit-password/Form.vue');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--file-name' => 'profile/edit-password',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }

    public function testViewFullFormGeneratorWithRouteShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/category/form.blade.php');
        $formVuePath = resource_path('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:full-form', [
            'table_name' => 'categories',
            '--route' => 'admin/categ-ories',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }
}
