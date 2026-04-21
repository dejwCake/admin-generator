<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class VueFormTest extends TestCase
{
    public function testVueFormGeneratorShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueFormGeneratorWithModelNameShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/billing-categ-ory/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueFormGeneratorWithFullModelNameShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/categ-ory/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueFormGeneratorWithBelongsToManyShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueFormGeneratorWithFileNameShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/profile-edit-password/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
            '--file-name' => 'profile/edit-password',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueFormGeneratorWithRouteShouldGenerateComponent(): void
    {
        $path = $this->app->resourcePath('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
