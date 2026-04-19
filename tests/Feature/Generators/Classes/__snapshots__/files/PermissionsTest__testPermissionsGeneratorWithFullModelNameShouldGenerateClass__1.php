<?php

declare(strict_types=1);

use Brackets\Craftable\Database\Migrations\PermissionMigration;
use Illuminate\Support\Collection;

return new class extends PermissionMigration
{
    public function __construct()
    {
        parent::__construct();

        $this->setPermissionsAndRoles(
            new Collection([
                'admin.cat',
                'admin.cat.index',
                'admin.cat.create',
                'admin.cat.show',
                'admin.cat.edit',
                'admin.cat.delete',
                'admin.cat.bulk-delete',
            ]),
            new Collection(),
        );
    }

    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up(): void
    {
        $this->migrateUp();
    }

    /**
     * Reverse the migrations.
     *
     * @throws Exception
     */
    public function down(): void
    {
        $this->migrateDown();
    }
};
