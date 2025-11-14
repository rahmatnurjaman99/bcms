<?php

namespace App\GraphQL\Resolvers;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class UserResolver
{
    /**
     * Paginate users with optional search by name or email.
     *
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function paginate(mixed $_, array $args): array
    {
        $page = max((int) Arr::get($args, 'page', 1), 1);
        $perPage = max((int) Arr::get($args, 'first', 10), 1);

        $query = User::query()->with(['roles', 'permissions'])->orderByDesc('created_at');

        $search = trim((string) Arr::get($args, 'search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'ilike', $like)
                    ->orWhere('email', 'ilike', $like);
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'paginatorInfo' => $this->paginatorInfo($paginator),
        ];
    }

    /**
     * Resolve the effective permissions for a user, including those inherited from roles.
     *
     * @return \Illuminate\Support\Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function permissions(User $user): Collection
    {
        return $user->getAllPermissions();
    }

    /**
     * Map paginator metadata to the Lighthouse PaginatorInfo structure.
     *
     * @return array<string, mixed>
     */
    private function paginatorInfo(LengthAwarePaginator $paginator): array
    {
        return [
            'count' => $paginator->count(),
            'currentPage' => $paginator->currentPage(),
            'firstItem' => $paginator->firstItem(),
            'lastItem' => $paginator->lastItem(),
            'hasMorePages' => $paginator->hasMorePages(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
