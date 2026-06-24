<?php

namespace Database\Factories;

use App\Models\Cycle;
use Illuminate\Database\Eloquent\Factories\Factory;

class CycleFactory extends Factory
{
    protected $model = Cycle::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2024, 2026);

        return [
            'name' => $year . ' ' . fake()->randomElement(['Regular Recruitment', 'Tradesmen Recruitment', 'Special Forces Recruitment', 'Officer Cadet Recruitment']),
            'cycle_code' => strtoupper(fake()->randomElement(['REG', 'TRD', 'SPC', 'OCD'])) . '-' . $year . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'start_date' => now()->subMonths(3),
            'end_date' => now()->subMonth(),
            'application_deadline' => now()->subMonths(2),
            'total_vacancies' => fake()->numberBetween(500, 2000),
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
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
