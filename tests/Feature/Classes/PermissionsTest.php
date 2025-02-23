<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Classes;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PermissionsTest extends TestCase
{
    use DatabaseMigrations;

    public function testPermissionsGeneratorShouldGenerateClass(): void
    {
        $permissionMigrationFile = 'fill_permissions_for_category.php';

        $this->artisan('admin:generate:permissions', [
            'table_name' => 'categories',
        ]);

        $filePath = $this->getPermissionMigrationPath($permissionMigrationFile);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testPermissionsGeneratorWithModelNameShouldGenerateClass(): void
    {
        $permissionMigrationFile = 'fill_permissions_for_cat.php';

        $this->artisan('admin:generate:permissions', [
            'table_name' => 'categories',
            '--model-name' => 'Billing\\Cat',
        ]);

        $filePath = $this->getPermissionMigrationPath($permissionMigrationFile);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testPermissionsGeneratorWithoutBulkShouldGenerateClass(): void
    {
        $permissionMigrationFile = 'fill_permissions_for_category.php';

        $this->artisan('admin:generate:permissions', [
            'table_name' => 'categories',
            '--without-bulk' => true,
        ]);

        $filePath = $this->getPermissionMigrationPath($permissionMigrationFile);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }
}
