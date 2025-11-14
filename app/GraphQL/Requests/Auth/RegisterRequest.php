<?php

namespace App\GraphQL\Requests\Auth;

use Illuminate\Support\Facades\Validator;

class RegisterRequest
{
    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public static function validate(array $args): array
    {
        $input = $args['input'] ?? [];

        if (array_key_exists('passwordConfirmation', $input)) {
            $input['password_confirmation'] = $input['passwordConfirmation'];
        }

        return Validator::make($input, self::rules())->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
            'deviceName' => ['nullable', 'string', 'max:255'],
        ];
    }
}
