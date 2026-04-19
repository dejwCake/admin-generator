<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VueFormTest extends TestCase
{
    use DatabaseMigrations;

    public function testVueFormGeneratorShouldGenerateComponent(): void
    {
        $path = resource_path('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testVueFormGeneratorWithModelNameShouldGenerateComponent(): void
    {
        $path = resource_path('js/admin/billing-categ-ory/Form.vue');

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
        $path = resource_path('js/admin/categ-ory/Form.vue');

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
        $path = resource_path('js/admin/category/Form.vue');

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
        $path = resource_path('js/admin/profile-edit-password/Form.vue');

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
        $path = resource_path('js/admin/category/Form.vue');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:vue-form', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
