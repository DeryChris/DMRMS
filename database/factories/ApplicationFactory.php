<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Cycle;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    private static array $institutions = [
        'University of Ghana', 'Kwame Nkrumah University of Science and Technology',
        'University of Cape Coast', 'University of Education, Winneba',
        'University for Development Studies', 'University of Professional Studies, Accra',
        'Ghana Institute of Management and Public Administration',
        'Accra Technical University', 'Kumasi Technical University',
        'Cape Coast Technical University', 'Takoradi Technical University',
        'Ho Technical University', 'Wa Polytechnic', 'Bolgatanga Technical University',
        'Sunyani Technical University', 'Tamale Technical University',
        'Koforidua Technical University', 'Ghana Armed Forces Command and Staff College',
        'National Defence College', 'Ghana Military Academy',
    ];

    private static array $qualifications = [
        'Bachelor of Arts', 'Bachelor of Science', 'Bachelor of Engineering',
        'Bachelor of Education', 'Bachelor of Laws', 'Bachelor of Business Administration',
        'Higher National Diploma', 'Diploma', 'WASSCE Certificate',
        'Master of Science', 'Master of Arts', 'Master of Business Administration',
    ];

    public function definition(): array
    {
        $year = fake()->numberBetween(2015, 2023);

        return [
            'applicant_id' => Applicant::factory(),
            'cycle_id' => Cycle::factory(),
            'gaf_id' => 'GAF-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'application_date' => now()->subDays(fake()->numberBetween(1, 90)),
            'education_level' => fake()->randomElement(['WASSCE', 'Degree', 'HND', 'Diploma']),
            'institution_name' => fake()->randomElement(self::$institutions),
            'qualification' => fake()->randomElement(self::$qualifications),
            'year_obtained' => (string) $year,
            'height' => fake()->randomFloat(2, 1.60, 1.85),
            'weight' => fake()->randomFloat(2, 55, 95),
            'criminal_record' => false,
            'fitness_status' => fake()->randomElement(['fit', 'fit', 'fit', 'unfit']),
            'status' => 'submitted',
            'submitted_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ]);
    }

    public function shortlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shortlisted',
            'submitted_at' => now()->subDays(fake()->numberBetween(30, 60)),
        ]);
    }

    public function eligible(): static
    {
        return $this->state(fn (array $attributes) => [
            'criminal_record' => false,
            'fitness_status' => 'fit',
            'status' => 'submitted',
            'submitted_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ]);
    }

    public function notEligible(): static
    {
        return $this->state(fn (array $attributes) => [
            'criminal_record' => true,
            'fitness_status' => 'unfit',
            'status' => 'submitted',
            'submitted_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ]);
    }
}
