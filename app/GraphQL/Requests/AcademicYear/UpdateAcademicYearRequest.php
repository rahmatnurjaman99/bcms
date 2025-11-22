<?php

declare(strict_types=1);

namespace App\GraphQL\Requests\AcademicYear;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\Validator;

class UpdateAcademicYearRequest
{
    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public static function validate(array $args, AcademicYear $academicYear): array
    {
        return Validator::make(
            $args,
            AcademicYear::rules((int) $academicYear->id),
            AcademicYear::messages(),
        )->validate();
    }
}
