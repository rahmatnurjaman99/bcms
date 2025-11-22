<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Previous year (completed)
        AcademicYear::create([
            'name' => '2023/2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'is_active' => false,
            'description' => 'Previous academic year - completed',
        ]);

        // Current year (active)
        AcademicYear::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
            'description' => 'Current active academic year',
        ]);

        // Next year (upcoming)
        AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
            'description' => 'Upcoming academic year',
        ]);
    }
}
