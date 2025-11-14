<?php

namespace App\GraphQL\Mutations;

use App\Enums\UserPermission;
use App\GraphQL\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Spatie\Permission\PermissionRegistrar;

class UserMutator
{
    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function updateUser(mixed $_, array $args, GraphQLContext $context): User
    {
        $targetUser = User::query()->findOrFail($args['id']);
        $actor = $context->user();

        if (! $actor) {
            throw new AuthorizationException('Unauthenticated.');
        }

        $canManageOthers = $actor->can(UserPermission::USERS_MANAGE->value);

        if ($actor->id !== $targetUser->id && ! $canManageOthers) {
            throw new AuthorizationException('You are not allowed to modify this user.');
        }

        $input = $args['input'] ?? [];

        $data = UpdateUserRequest::validate($input, $targetUser);

        if (! $canManageOthers) {
            if (array_key_exists('roles', $data)) {
                throw new AuthorizationException('You cannot change roles.');
            }

            if (array_key_exists('status', $data)) {
                throw new AuthorizationException('You cannot change status.');
            }
        }

        if (array_key_exists('name', $data)) {
            $targetUser->name = $data['name'] ?? $targetUser->name;
        }

        if (array_key_exists('email', $data)) {
            $targetUser->email = $data['email'] ?? $targetUser->email;
        }

        if (array_key_exists('status', $data)) {
            $targetUser->status = $data['status'];
        }

        if (! empty($data['password'])) {
            $targetUser->password = Hash::make($data['password']);
        }

        $targetUser->save();

        if ($canManageOthers && array_key_exists('roles', $data)) {
            $targetUser->syncRoles($data['roles'] ?? []);
            $this->permissionRegistrar->forgetCachedPermissions();
        }

        return $targetUser->load('roles', 'permissions');
    }

    public function deleteUser(mixed $_, array $args): bool
    {
        $user = User::query()->findOrFail($args['id']);
        $user->tokens()->delete();
        $deleted = (bool) $user->delete();
        $this->permissionRegistrar->forgetCachedPermissions();

        return $deleted;
    }
}
