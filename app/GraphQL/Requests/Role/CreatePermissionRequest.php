<?php

namespace App\GraphQL\Requests\Role;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreatePermissionRequest
{
    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public static function validate(array $args, string $guard): array
    {
        $input = $args['input'] ?? [];

        return Validator::make($input, self::rules($guard))->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(string $guard): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(config('permission.table_names.permissions'), 'name')
                    ->where(fn ($query) => $query->where('guard_name', $guard)),
            ],
        ];
    }
}
