<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserQuery
{
    /**
     * Build the base query for the `users` field.
     */
    public function users(mixed $_, array $args): Builder
    {
        $builder = User::query();

        $search = trim((string) ($args['search'] ?? ''));

        if ($search !== '') {
            $like = '%'.$search.'%';

            $builder->where(function (Builder $query) use ($like) {
                $query
                    ->where('name', 'ilike', $like)
                    ->orWhere('email', 'ilike', $like);
            });
        }

        return $builder;
    }
}
