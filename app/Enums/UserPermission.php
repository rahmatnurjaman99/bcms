<?php

namespace App\Enums;

/**
 * Fine grained permissions enforced throughout the API.
 */
enum UserPermission: string
{
    case USERS_VIEW = 'users.view';
    case USERS_MANAGE = 'users.manage';
    case SITES_VIEW = 'sites.view';
    case SITES_MANAGE = 'sites.manage';
    case ROLES_MANAGE = 'roles.manage';
    case ACTIVITY_VIEW = 'activity.view';
    case ACADEMIC_YEARS_VIEW = 'academic_years.view';
    case ACADEMIC_YEARS_MANAGE = 'academic_years.manage';
}
