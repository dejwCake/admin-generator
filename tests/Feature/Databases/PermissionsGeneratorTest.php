<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Databases;

use Brackets\AdminGenerator\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PermissionsGeneratorTest extends TestCase
{
    use DatabaseMigrations;

    public function testPermissionsGenerationShouldGenerateMigration(): void
    {
        $permissionMigrationFile = 'fill_permissions_for_category.php';

        $this->artisan('admin:generate:permissions', [
            'table_name' => 'categories',
        ]);

        $permissionMigrationPath = $this->getPermissionMigrationPath($permissionMigrationFile);

        self::assertFileExists($permissionMigrationPath);

        self::assertMatchesFileSnapshot($permissionMigrationPath);
    }
}
