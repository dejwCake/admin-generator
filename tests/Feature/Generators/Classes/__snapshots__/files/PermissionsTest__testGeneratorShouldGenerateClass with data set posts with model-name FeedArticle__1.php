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
                'admin.feed.article',
                'admin.feed.article.index',
                'admin.feed.article.create',
                'admin.feed.article.show',
                'admin.feed.article.edit',
                'admin.feed.article.delete',
                'admin.feed.article.bulk-delete',
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
