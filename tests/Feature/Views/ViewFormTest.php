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
        $formPath = resource_path('views/admin/category/components/form-elements.blade.php');
        $formRightPath = resource_path('views/admin/category/components/form-elements-right.blade.php');
        $createPath = resource_path('views/admin/category/create.blade.php');
        $editPath = resource_path('views/admin/category/edit.blade.php');
        $formJsPath = resource_path('js/admin/category/Form.js');
        $indexJsPath = resource_path('js/admin/category/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formRightPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($bootstrapJsPath);


        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formRightPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($bootstrapJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formRightPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }

    public function testViewFormGeneratorWithModelNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/billing/categ-ory/components/form-elements.blade.php');
        $formRightPath = resource_path('views/admin/billing/categ-ory/components/form-elements-right.blade.php');
        $createPath = resource_path('views/admin/billing/categ-ory/create.blade.php');
        $editPath = resource_path('views/admin/billing/categ-ory/edit.blade.php');
        $formJsPath = resource_path('js/admin/billing-categ-ory/Form.js');
        $indexJsPath = resource_path('js/admin/billing-categ-ory/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formRightPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($bootstrapJsPath);

        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formRightPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($bootstrapJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formRightPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }

    public function testViewFormGeneratorWithFullModelNameShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/categ-ory/components/form-elements.blade.php');
        $formRightPath = resource_path('views/admin/categ-ory/components/form-elements-right.blade.php');
        $createPath = resource_path('views/admin/categ-ory/create.blade.php');
        $editPath = resource_path('views/admin/categ-ory/edit.blade.php');
        $formJsPath = resource_path('js/admin/categ-ory/Form.js');
        $indexJsPath = resource_path('js/admin/categ-ory/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formRightPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($bootstrapJsPath);

        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formRightPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($bootstrapJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formRightPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }

    public function testViewFormGeneratorWithBelongsToManyShouldGenerateViews(): void
    {
        $formPath = resource_path('views/admin/category/components/form-elements.blade.php');
        $formRightPath = resource_path('views/admin/category/components/form-elements-right.blade.php');
        $createPath = resource_path('views/admin/category/create.blade.php');
        $editPath = resource_path('views/admin/category/edit.blade.php');
        $formJsPath = resource_path('js/admin/category/Form.js');
        $indexJsPath = resource_path('js/admin/category/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        self::assertFileDoesNotExist($formPath);
        self::assertFileDoesNotExist($formRightPath);
        self::assertFileDoesNotExist($createPath);
        self::assertFileDoesNotExist($editPath);
        self::assertFileDoesNotExist($formJsPath);
        self::assertFileDoesNotExist($indexJsPath);
        self::assertFileDoesNotExist($bootstrapJsPath);


        $this->artisan('admin:generate:form', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertFileExists($formPath);
        self::assertFileExists($formRightPath);
        self::assertFileExists($createPath);
        self::assertFileExists($editPath);
        self::assertFileExists($formJsPath);
        self::assertFileExists($indexJsPath);
        self::assertFileExists($bootstrapJsPath);
        self::assertMatchesFileSnapshot($formPath);
        self::assertMatchesFileSnapshot($formRightPath);
        self::assertMatchesFileSnapshot($createPath);
        self::assertMatchesFileSnapshot($editPath);
        self::assertMatchesFileSnapshot($formJsPath);
        self::assertMatchesFileSnapshot($indexJsPath);
        self::assertMatchesFileSnapshot($bootstrapJsPath);
    }
}
