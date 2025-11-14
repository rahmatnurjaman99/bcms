<?php

namespace App\GraphQL\Requests\User;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateUserRequest
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function validate(array $input, User $targetUser): array
    {
        return Validator::make($input, self::rules($targetUser))->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(User $targetUser): array
    {
        $defaultGuard = config('auth.defaults.guard', 'web');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'email:rfc,dns',
                Rule::unique('users', 'email')->ignore($targetUser->id),
            ],
            'status' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => [
                'string',
                Rule::exists(config('permission.table_names.roles'), 'name')
                    ->where(fn ($query) => $query->where('guard_name', $defaultGuard)),
            ],
        ];
    }
}
