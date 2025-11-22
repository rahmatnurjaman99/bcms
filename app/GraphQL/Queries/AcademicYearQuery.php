<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\AcademicYear;

class AcademicYearQuery
{
    /**
     * Get the currently active academic year.
     */
    public function active(): ?AcademicYear
    {
        return AcademicYear::query()->active()->first();
    }
}
