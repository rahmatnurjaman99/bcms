<?php

namespace Database\Seeders;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        foreach (UserPermission::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => $guard,
            ]);
        }

        foreach (UserRole::cases() as $role) {
            $roleModel = Role::firstOrCreate([
                'name' => $role->value,
                'guard_name' => $guard,
            ]);

            $roleModel->syncPermissions(
                array_map(
                    static fn (UserPermission $permission) => $permission->value,
                    $role->permissions()
                )
            );
        }
    }
}
