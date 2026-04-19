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
                'admin.billing.cat',
                'admin.billing.cat.index',
                'admin.billing.cat.create',
                'admin.billing.cat.show',
                'admin.billing.cat.edit',
                'admin.billing.cat.delete',
                'admin.billing.cat.bulk-delete',
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
