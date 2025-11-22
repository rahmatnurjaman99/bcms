<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\GraphQL\Requests\AcademicYear\CreateAcademicYearRequest;
use App\GraphQL\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Models\AcademicYear;

class AcademicYearMutation
{
    public function create(mixed $_, array $args): AcademicYear
    {
        $data = CreateAcademicYearRequest::validate($args);

        return AcademicYear::query()->create($data);
    }

    public function update(mixed $_, array $args): AcademicYear
    {
        $academicYear = AcademicYear::query()->findOrFail($args['id']);

        $data = UpdateAcademicYearRequest::validate($args, $academicYear);

        $academicYear->fill($data)->save();

        return $academicYear->fresh();
    }

    public function setActive(mixed $_, array $args): AcademicYear
    {
        $academicYear = AcademicYear::query()->findOrFail($args['id']);

        $academicYear->activate();

        return $academicYear->fresh();
    }
}
