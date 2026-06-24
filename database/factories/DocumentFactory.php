<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    private static array $documentTypes = [
        'birth_certificate', 'educational_certificate', 'national_id',
        'passport_photograph', 'medical_report', 'police_clearance',
        'birth_certificate', 'educational_certificate',
    ];

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'document_type' => fake()->randomElement(self::$documentTypes),
            'file_name' => 'doc_' . Str::random(10) . '.' . fake()->fileExtension(),
            'file_path' => 'documents/' . date('Y/m/d') . '/' . Str::random(20),
            'file_size' => fake()->numberBetween(100000, 2000000),
            'mime_type' => fake()->randomElement(['image/jpeg', 'image/jpeg', 'application/pdf']),
            'upload_date' => now()->subDays(fake()->numberBetween(0, 30)),
            'verification_status' => 'pending',
            'ai_verified' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
            'ai_verified' => true,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'rejected',
            'ai_verified' => true,
        ]);
    }
}
