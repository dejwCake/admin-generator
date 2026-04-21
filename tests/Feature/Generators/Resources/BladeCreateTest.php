<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Resources;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class BladeCreateTest extends TestCase
{
    public function testBladeCreateGeneratorShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/create.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-create', [
            'table_name' => 'categories',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeCreateGeneratorWithModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/billing/categ-ory/create.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-create', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeCreateGeneratorWithFullModelNameShouldGenerateView(): void
    {
        $path = resource_path('views/admin/categ-ory/create.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-create', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\CategOry',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }

    public function testBladeCreateGeneratorWithBelongsToManyShouldGenerateView(): void
    {
        $path = resource_path('views/admin/category/create.blade.php');

        self::assertFileDoesNotExist($path);

        $this->artisan('admin:generate:blade-create', [
            'table_name' => 'categories',
            '--belongs-to-many' => 'posts',
        ]);

        self::assertFileExists($path);
        self::assertMatchesFileSnapshot($path);
    }
}
