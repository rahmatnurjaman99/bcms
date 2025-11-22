<?php

namespace App\GraphQL\Requests\Role;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UpdatePermissionRequest
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function validate(array $input, Permission $permission, string $guard): array
    {
        return Validator::make($input, self::rules($permission, $guard))->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(Permission $permission, string $guard): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(config('permission.table_names.permissions'), 'name')
                    ->ignore($permission->id)
                    ->where(fn($query) => $query->where('guard_name', $guard)),
            ],
            'guard' => ['nullable', 'string', 'max:255'],
        ];
    }
}
