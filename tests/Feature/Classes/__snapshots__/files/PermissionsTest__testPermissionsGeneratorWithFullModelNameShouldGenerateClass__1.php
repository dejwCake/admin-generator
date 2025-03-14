<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private Config $config;
    private Cache $cache;
    private string $guardName;

    /** @var array<array<string, string|CarbonImmutable>> */
    private array $permissions;

    /** @var array<int, array<string, Collection<int, string>|string>> */
    private array $roles;

    public function __construct()
    {
        $this->config = app(Config::class);
        $this->cache = app(Cache::class);
        $this->guardName = $this->config->get('admin-auth.defaults.guard');

        $permissions = new Collection([
            'admin.cat',
            'admin.cat.index',
            'admin.cat.create',
            'admin.cat.show',
            'admin.cat.edit',
            'admin.cat.delete',
            'admin.cat.bulk-delete',
        ]);

        //Add New permissions
        $this->permissions = $permissions->map(fn (string $permission) => [
                'name' => $permission,
                'guard_name' => $this->guardName,
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
            ])->toArray();

        //Role should already exist
        $this->roles = [
            [
                'name' => 'Administrator',
                'guard_name' => $this->guardName,
                'permissions' => $permissions,
            ],
        ];
    }

    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up(): void
    {
        $tableNames = $this->config->get(
            'permission.table_names',
            [
                'roles' => 'roles',
                'permissions' => 'permissions',
                'model_has_permissions' => 'model_has_permissions',
                'model_has_roles' => 'model_has_roles',
                'role_has_permissions' => 'role_has_permissions',
            ],
        );

        DB::transaction(function () use ($tableNames): void {
            foreach ($this->permissions as $permission) {
                $permissionItem = DB::table($tableNames['permissions'])
                    ->where([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ])->first();
                if ($permissionItem === null) {
                    DB::table($tableNames['permissions'])->insert($permission);
                }
            }

            foreach ($this->roles as $role) {
                $permissions = $role['permissions'];
                unset($role['permissions']);

                $roleItem = DB::table($tableNames['roles'])
                    ->where([
                        'name' => $role['name'],
                        'guard_name' => $role['guard_name'],
                    ])->first();
                if ($roleItem !== null) {
                    $roleId = $roleItem->id;

                    $permissionItems = DB::table($tableNames['permissions'])
                        ->whereIn('name', $permissions)
                        ->where(
                            'guard_name',
                            $role['guard_name'],
                        )->get();
                    foreach ($permissionItems as $permissionItem) {
                        $roleHasPermissionData = [
                            'permission_id' => $permissionItem->id,
                            'role_id' => $roleId,
                        ];
                        $roleHasPermissionItem = DB::table($tableNames['role_has_permissions'])
                            ->where($roleHasPermissionData)
                            ->first();
                        if ($roleHasPermissionItem === null) {
                            DB::table($tableNames['role_has_permissions'])->insert($roleHasPermissionData);
                        }
                    }
                }
            }
        });
        $this->cache->forget($this->config->get('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @throws Exception
     */
    public function down(): void
    {
        $tableNames = $this->config->get(
            'permission.table_names',
            [
                'roles' => 'roles',
                'permissions' => 'permissions',
                'model_has_permissions' => 'model_has_permissions',
                'model_has_roles' => 'model_has_roles',
                'role_has_permissions' => 'role_has_permissions',
            ],
        );

        DB::transaction(function () use ($tableNames): void {
            foreach ($this->permissions as $permission) {
                $permissionItem = DB::table($tableNames['permissions'])
                    ->where([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ])->first();
                if ($permissionItem !== null) {
                    DB::table($tableNames['permissions'])
                        ->where('id', $permissionItem->id)
                        ->delete();
                    DB::table($tableNames['model_has_permissions'])
                        ->where('permission_id', $permissionItem->id)
                        ->delete();
                }
            }
        });
        $this->cache->forget($this->config->get('permission.cache.key'));
    }
};
