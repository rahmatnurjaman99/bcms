<?php

namespace App\GraphQL\Requests\Auth;

use Illuminate\Support\Facades\Validator;

class LoginRequest
{
    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public static function validate(array $args): array
    {
        return Validator::make($args, self::rules())->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'password' => ['required', 'string'],
            'deviceName' => ['nullable', 'string', 'max:255'],
        ];
    }
}
