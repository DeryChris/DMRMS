<?php

namespace Database\Seeders;

use App\Models\Cycle;
use Illuminate\Database\Seeder;

class CycleSeeder extends Seeder
{
    public function run(): void
    {
        Cycle::create([
            'name' => '2026 Regular Recruitment',
            'cycle_code' => 'REG-2026-001',
            'start_date' => now()->subMonths(3)->format('Y-m-d'),
            'end_date' => now()->subMonth()->format('Y-m-d'),
            'application_deadline' => now()->subMonths(2),
            'total_vacancies' => 1500,
            'requirements' => [
                'age_min' => 18,
                'age_max' => 26,
                'height_min_male' => 1.68,
                'height_min_female' => 1.60,
                'education_min' => 'WASSCE',
                'nationality' => 'Ghanaian',
            ],
            'ai_enabled' => true,
            'status' => 'active',
        ]);

        Cycle::create([
            'name' => '2026 Tradesmen Recruitment',
            'cycle_code' => 'TRD-2026-001',
            'start_date' => now()->subMonths(2)->format('Y-m-d'),
            'end_date' => now()->addMonths(1)->format('Y-m-d'),
            'application_deadline' => now()->addWeeks(2),
            'total_vacancies' => 800,
            'requirements' => [
                'age_min' => 18,
                'age_max' => 30,
                'height_min_male' => 1.65,
                'height_min_female' => 1.58,
                'education_min' => 'BECE',
                'nationality' => 'Ghanaian',
            ],
            'ai_enabled' => true,
            'status' => 'active',
        ]);

        Cycle::create([
            'name' => '2025 Regular Recruitment',
            'cycle_code' => 'REG-2025-001',
            'start_date' => now()->subMonths(15)->format('Y-m-d'),
            'end_date' => now()->subMonths(13)->format('Y-m-d'),
            'application_deadline' => now()->subMonths(14),
            'total_vacancies' => 1200,
            'requirements' => [
                'age_min' => 18,
                'age_max' => 26,
                'height_min_male' => 1.68,
                'height_min_female' => 1.60,
                'education_min' => 'WASSCE',
                'nationality' => 'Ghanaian',
            ],
            'ai_enabled' => true,
            'status' => 'archived',
        ]);
    }
}
