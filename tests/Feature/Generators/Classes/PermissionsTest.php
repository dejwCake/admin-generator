<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;

class PermissionsTest extends TestCase
{
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

    public function testPermissionsGeneratorWithFullModelNameShouldGenerateClass(): void
    {
        $permissionMigrationFile = 'fill_permissions_for_cat.php';

        $this->artisan('admin:generate:permissions', [
            'table_name' => 'categories',
            '--model-name' => 'App\\Billing\\Cat',
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
