@php echo "<?php"
@endphp


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
                'admin.{{ $modelDotNotation }}',
                'admin.{{ $modelDotNotation }}.index',
                'admin.{{ $modelDotNotation }}.create',
                'admin.{{ $modelDotNotation }}.show',
                'admin.{{ $modelDotNotation }}.edit',
                'admin.{{ $modelDotNotation }}.delete',
@if(!$withoutBulk)
                'admin.{{ $modelDotNotation }}.bulk-delete',
@endif
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
