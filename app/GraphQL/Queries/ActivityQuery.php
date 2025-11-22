<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityQuery
{
    /**
     * Build a filtered activity query.
     */
    public function activities(mixed $_, array $args): Builder
    {
        $builder = Activity::query()->latest('created_at');

        if (! empty($args['logName'])) {
            $builder->where('log_name', $args['logName']);
        }

        if (!empty($args['event'])) {
            $builder->where('event', $args['event']);
        }

        $search = trim((string) ($args['search'] ?? ''));
        if ($search !== '') {
            $builder->where('description', 'ilike', '%'.$search.'%');
        }

        if (!empty($args['subject_type'])) {
            $builder->where('subject_type', $args['subject_type']);
        }

        if (!empty($args['subject_id'])) {
            $builder->where('subject_id', $args['subject_id']);
        }

        if (!empty($args['causer_id'])) {
            $builder->where('causer_id', $args['causer_id']);
        }

        if (!empty($args['batch_uuid'])) {
            $builder->where('batch_uuid', $args['batch_uuid']);
        }

        if (!empty($args['createdFrom'])) {
            $builder->whereDate('created_at', '>=', $args['createdFrom']);
        }

        if (!empty($args['createdTo'])) {
            $builder->whereDate('created_at', '<=', $args['createdTo']);
        }

        return $builder->with('causer');
    }
}
