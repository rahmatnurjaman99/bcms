<?php

namespace App\GraphQL\Mutations;

use App\GraphQL\Requests\Role\CreatePermissionRequest;
use App\GraphQL\Requests\Role\CreateRoleRequest;
use App\GraphQL\Requests\Role\UpdatePermissionRequest;
use App\GraphQL\Requests\Role\UpdateRoleRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleMutator
{
    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function createRole(mixed $_, array $args): Role
    {
        $guard = $this->guardName($args);

        $data = CreateRoleRequest::validate($args, $guard);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $guard,
        ]);

        $permissionNames = $data['permissions'] ?? [];
        if ($permissionNames) {
            $this->syncRolePermissions($role, $permissionNames, $guard);
        }

        $this->permissionRegistrar->forgetCachedPermissions();

        return $role->load('permissions');
    }

    public function createPermission(mixed $_, array $args): Permission
    {
        $guard = $this->guardName($args);

        $data = CreatePermissionRequest::validate($args, $guard);

        $permission = Permission::create([
            'name' => $data['name'],
            'guard_name' => $guard,
        ]);

        $this->permissionRegistrar->forgetCachedPermissions();

        return $permission;
    }

    public function updateRole(mixed $_, array $args): Role
    {
        $role = Role::query()->findOrFail($args['id']);

        $input = $args['input'] ?? [];
        $guard = Arr::get($input, 'guard', $role->guard_name);

        $data = UpdateRoleRequest::validate($input, $role, $guard);

        $updates = [];
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $updates['name'] = $data['name'];
        }
        if (array_key_exists('guard', $data)) {
            $updates['guard_name'] = $guard;
        }

        if ($updates) {
            $role->fill($updates)->save();
        }

        if (array_key_exists('permissions', $data)) {
            $this->syncRolePermissions($role, $data['permissions'] ?? [], $guard);
        }

        $this->permissionRegistrar->forgetCachedPermissions();

        return $role->load('permissions');
    }

    public function deleteRole(mixed $_, array $args): bool
    {
        $role = Role::query()->findOrFail($args['id']);
        $deleted = (bool) $role->delete();
        $this->permissionRegistrar->forgetCachedPermissions();

        return $deleted;
    }

    public function updatePermission(mixed $_, array $args): Permission
    {
        $permission = Permission::query()->findOrFail($args['id']);
        $input = $args['input'] ?? [];
        $guard = Arr::get($input, 'guard', $permission->guard_name);

        $data = UpdatePermissionRequest::validate($input, $permission, $guard);

        $updates = [];
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $updates['name'] = $data['name'];
        }
        if (array_key_exists('guard', $data)) {
            $updates['guard_name'] = $guard;
        }

        if ($updates) {
            $permission->fill($updates)->save();
        }

        $this->permissionRegistrar->forgetCachedPermissions();

        return $permission;
    }

    public function deletePermission(mixed $_, array $args): bool
    {
        $permission = Permission::query()->findOrFail($args['id']);
        $deleted = (bool) $permission->delete();
        $this->permissionRegistrar->forgetCachedPermissions();

        return $deleted;
    }

    private function guardName(array $args): string
    {
        return Arr::get($args, 'input.guard')
            ?: config('auth.defaults.guard', 'web');
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function syncRolePermissions(Role $role, array $permissionNames, string $guard): void
    {
        if (! $permissionNames) {
            $role->syncPermissions([]);

            return;
        }

        $available = Permission::query()
            ->whereIn('name', $permissionNames)
            ->where('guard_name', $guard)
            ->pluck('name')
            ->all();

        $missing = array_values(array_diff($permissionNames, $available));
        if ($missing) {
            throw ValidationException::withMessages([
                'permissions' => ['Unknown permissions: '.implode(', ', $missing)],
            ]);
        }

        $role->syncPermissions($permissionNames);
    }
}
