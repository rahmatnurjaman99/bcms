<?php

namespace App\GraphQL\Requests\Auth;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SocialLoginRequest
{
    /**
     * @param  array<string, mixed>  $args
     * @param  array<int, string>  $allowedProviders
     * @return array<string, mixed>
     */
    public static function validate(array $args, array $allowedProviders): array
    {
        $normalized = $args;
        $provider = Arr::get($args, 'provider');

        if (is_string($provider)) {
            $normalized['provider'] = strtolower($provider);
        }

        return Validator::make($normalized, self::rules($allowedProviders))->validate();
    }

    /**
     * @param  array<int, string>  $allowedProviders
     * @return array<string, mixed>
     */
    public static function rules(array $allowedProviders): array
    {
        return [
            'provider' => ['required', 'string', Rule::in($allowedProviders)],
            'accessToken' => ['required', 'string'],
            'deviceName' => ['nullable', 'string', 'max:255'],
        ];
    }
}
