<?php

namespace App\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleQuery
{
    /**
     * Resolve the list of roles with optional search support.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    public function roles(mixed $_, array $args)
    {
        $builder = Role::query()->with('permissions');

        $search = trim((string) ($args['search'] ?? ''));

        if ($search !== '') {
            $like = '%'.$search.'%';

            $builder->where(function (Builder $query) use ($like) {
                $query->where('name', 'ilike', $like)
                    ->orWhereHas('permissions', function (Builder $permissionQuery) use ($like) {
                        $permissionQuery->where('name', 'ilike', $like);
                    });
            });
        }

        return $builder->get();
    }
}
