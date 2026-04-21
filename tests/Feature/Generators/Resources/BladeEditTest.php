<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class BladeEditTest extends TestCase
{
    public function testBladeEditGeneratorShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/edit.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-edit', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeEditGeneratorWithModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/billing/categ-ory/edit.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-edit', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeEditGeneratorWithFullModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/categ-ory/edit.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-edit', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeEditGeneratorWithBelongsToManyShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/edit.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-edit', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
