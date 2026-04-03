<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Views;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewFormTest extends TestCase
{
    use DatabaseMigrations;

    public function testViewFormGeneratorShouldGenerateViews(): void
    {
        $createPath = resource_path('views/admin/category/create.blade.php');
        $editPath = resource_path('views/admin/category/edit.blade.php');
        $formVuePath = resource_path('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);


        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }

    public function testViewFormGeneratorWithModelNameShouldGenerateViews(): void
    {
        $createPath = resource_path('views/admin/billing/categ-ory/create.blade.php');
        $editPath = resource_path('views/admin/billing/categ-ory/edit.blade.php');
        $formVuePath = resource_path('js/admin/billing-categ-ory/Form.vue');

        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }

    public function testViewFormGeneratorWithFullModelNameShouldGenerateViews(): void
    {
        $createPath = resource_path('views/admin/categ-ory/create.blade.php');
        $editPath = resource_path('views/admin/categ-ory/edit.blade.php');
        $formVuePath = resource_path('js/admin/categ-ory/Form.vue');

        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);

        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }

    public function testViewFormGeneratorWithBelongsToManyShouldGenerateViews(): void
    {
        $createPath = resource_path('views/admin/category/create.blade.php');
        $editPath = resource_path('views/admin/category/edit.blade.php');
        $formVuePath = resource_path('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formVuePath);


        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formVuePath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formVuePath);
    }
}
