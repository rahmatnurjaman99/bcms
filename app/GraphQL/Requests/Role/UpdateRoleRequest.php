<?php

namespace App\GraphQL\Requests\Role;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function validate(array $input, Role $role, string $guard): array
    {
        return Validator::make($input, self::rules($role, $guard))->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(Role $role, string $guard): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(config('permission.table_names.roles'), 'name')
                    ->ignore($role->id)
                    ->where(fn ($query) => $query->where('guard_name', $guard)),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'guard' => ['nullable', 'string', 'max:255'],
        ];
    }
}
