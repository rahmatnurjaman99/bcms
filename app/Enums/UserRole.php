<?php

namespace App\Enums;

/**
 * Application level roles used for authorization.
 */
enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * @return array<UserPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SUPERADMIN => UserPermission::cases(),
            self::ADMIN => [
                UserPermission::USERS_VIEW,
                UserPermission::USERS_MANAGE,
                UserPermission::SITES_VIEW,
                UserPermission::SITES_MANAGE,
                UserPermission::ACTIVITY_VIEW,
                UserPermission::ACADEMIC_YEARS_VIEW,
                UserPermission::ACADEMIC_YEARS_MANAGE,
            ],
            self::USER => [
                UserPermission::SITES_VIEW,
                UserPermission::ACADEMIC_YEARS_VIEW,
            ],
        };
    }

    public static function default(): self
    {
        return self::USER;
    }
}
