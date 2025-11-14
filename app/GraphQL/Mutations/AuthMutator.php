<?php

namespace App\GraphQL\Mutations;

use App\Enums\UserRole;
use App\GraphQL\Requests\Auth\LoginRequest;
use App\GraphQL\Requests\Auth\RegisterRequest;
use App\GraphQL\Requests\Auth\SocialLoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Throwable;

class AuthMutator
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function register(mixed $_, array $args): array
    {
        $data = RegisterRequest::validate($args);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole(UserRole::default()->value);

        event(new Registered($user));

        return $this->buildAuthPayload($user, $data['deviceName'] ?? 'graphql-client');
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function login(mixed $_, array $args): array
    {
        $credentials = LoginRequest::validate($args);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        return $this->buildAuthPayload($user, $credentials['deviceName'] ?? 'graphql-client');
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function socialLogin(mixed $_, array $args): array
    {
        $data = SocialLoginRequest::validate($args, $this->socialProviders());

        try {
            $socialiteUser = Socialite::driver($data['provider'])
                ->stateless()
                ->userFromToken($data['accessToken']);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'accessToken' => ['Unable to authenticate with '.$data['provider'].'.'],
            ]);
        }

        $email = $socialiteUser->getEmail();

        if (! $email) {
            throw ValidationException::withMessages([
                'provider' => ['No email address returned by '.$data['provider'].'.'],
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: 'Unknown User',
                'password' => Hash::make(Str::random(40)),
                'google_id' => $data['provider'] === 'google' ? (string) $socialiteUser->getId() : null,
                'avatar_url' => $socialiteUser->getAvatar(),
            ]
        );

        $profileUpdates = [];

        if ($data['provider'] === 'google' && $socialiteUser->getId() && $user->google_id !== (string) $socialiteUser->getId()) {
            $profileUpdates['google_id'] = (string) $socialiteUser->getId();
        }

        if ($socialiteUser->getAvatar()) {
            $profileUpdates['avatar_url'] = $socialiteUser->getAvatar();
        }

        if (! empty($profileUpdates)) {
            $user->forceFill($profileUpdates)->save();
        }

        if ($user->wasRecentlyCreated || ! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if (! $user->hasRole(UserRole::default()->value)) {
            $user->assignRole(UserRole::default()->value);
        }

        return $this->buildAuthPayload(
            $user,
            $data['deviceName'] ?? sprintf('%s-oauth', $data['provider'])
        );
    }

    public function logout(mixed $_, array $args, GraphQLContext $context): bool
    {
        $user = $context->user();

        if (! $user) {
            return false;
        }

        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        } else {
            $user->tokens()->delete();
        }

        return true;
    }

    private function buildAuthPayload(User $user, string $deviceName): array
    {
        $token = $user->createToken($deviceName ?: 'graphql-client');

        return [
            'token' => $token->plainTextToken,
            'user' => $user,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function socialProviders(): array
    {
        return config('services.socialite.providers', ['google']);
    }
}
