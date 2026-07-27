<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Cycle;
use App\Models\Document;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApplicantSeeder extends Seeder
{
    private $faker;
    private static array $regions = [
        'Greater Accra', 'Ashanti', 'Western', 'Western North', 'Central',
        'Eastern', 'Volta', 'Oti', 'Northern', 'Savannah', 'North East',
        'Upper East', 'Upper West', 'Bono', 'Bono East', 'Ahafo',
    ];

    private static array $districts = [
        'Accra Metropolitan', 'Kumasi Metropolitan', 'Sekondi Takoradi Metropolitan',
        'Tamale Metropolitan', 'Cape Coast Metropolitan', 'Tema Metropolitan',
        'Obuasi Municipal', 'Koforidua Municipal', 'Ho Municipal', 'Wa Municipal',
        'Bolgatanga Municipal', 'Sunyani Municipal', 'Techiman Municipal',
        'Nalerigu', 'Damongo', 'Dambai',
    ];

    private static array $institutions = [
        'University of Ghana', 'Kwame Nkrumah University of Science and Technology',
        'University of Cape Coast', 'University of Education, Winneba',
        'University for Development Studies', 'Accra Technical University',
        'Kumasi Technical University', 'Ghana Military Academy',
    ];

    private static array $firstNames = [
        'Kwame', 'Yaa', 'Kofi', 'Ama', 'Samuel', 'Grace', 'Emmanuel', 'Mary',
        'Michael', 'Patricia', 'David', 'Elizabeth', 'Joseph', 'Sarah', 'John',
    ];

    private static array $lastNames = [
        'Mensah', 'Agyapong', 'Ansah', 'Osei', 'Owusu', 'Sarpong', 'Boateng',
        'Asante', 'Appiah', 'Yeboah', 'Adjei', 'Antwi', 'Darko', 'Opoku',
    ];

    public function run(): void
    {
        $this->faker = Faker::create();
        $activeCycle = Cycle::where('status', 'active')->first();
        if (!$activeCycle) {
            $activeCycle = Cycle::factory()->create(['status' => 'active']);
        }

        // 3 applicants with draft applications
        for ($i = 0; $i < 3; $i++) {
            $applicant = $this->createApplicant();
            $this->createApplication($applicant, $activeCycle, 'draft', false, null, false);
        }

        // 3 applicants with submitted eligible applications
        for ($i = 0; $i < 3; $i++) {
            $applicant = $this->createApplicant();
            $application = $this->createApplication($applicant, $activeCycle, 'submitted', false, 'fit', true);
            $this->createDocuments($application);
        }

        // 2 applicants with submitted not eligible applications
        for ($i = 0; $i < 2; $i++) {
            $applicant = $this->createApplicant();
            $criminalRecord = $i === 0;
            $fitnessStatus = $i === 0 ? 'fit' : 'unfit';
            $application = $this->createApplication($applicant, $activeCycle, 'submitted', $criminalRecord, $fitnessStatus, true);
            $this->createDocuments($application);
        }

        // 2 applicants with complete flow (shortlisted)
        for ($i = 0; $i < 2; $i++) {
            $applicant = $this->createApplicant();
            $application = $this->createApplication($applicant, $activeCycle, 'shortlisted', false, 'fit', true);
            $this->createDocuments($application);
        }
    }

    private function createApplicant(): Applicant
    {
        $firstName = $this->faker->randomElement(self::$firstNames);
        $lastName = $this->faker->randomElement(self::$lastNames);
        $region = $this->faker->randomElement(self::$regions);
        $gender = $this->faker->randomElement(['Male', 'Female']);

        return Applicant::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'other_names' => $this->faker->optional(0.5)->firstName(),
            'date_of_birth' => $this->faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'gender' => $gender,
            'marital_status' => 'Single',
            'contact_number' => '0' . $this->faker->numerify('#########'),
            'email' => strtolower($firstName . '.' . $lastName . $this->uniqueSuffix() . '@example.com'),
            'residential_address' => 'P.O. Box ' . $this->faker->numberBetween(100, 9999) . ', ' . $region,
            'region' => $region,
            'district' => $this->faker->randomElement(self::$districts),
            'nationality' => 'Ghanaian',
            'national_id' => 'GHA-' . $this->faker->unique()->numerify('##########'),
            'password' => Hash::make('change-me-applicant-2026'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
    }

    private function createApplication(Applicant $applicant, Cycle $cycle, string $status, bool $criminalRecord, ?string $fitnessStatus, bool $withSubmittedAt): Application
    {
        $data = [
            'applicant_id' => $applicant->id,
            'cycle_id' => $cycle->id,
            'gaf_id' => 'GAF-' . date('Y') . '-' . str_pad(Application::count() + 1, 6, '0', STR_PAD_LEFT),
            'application_date' => now()->subDays($this->faker->numberBetween(1, 90)),
            'education_level' => $this->faker->randomElement(['WASSCE', 'Degree', 'HND', 'Diploma']),
            'institution_name' => $this->faker->randomElement(self::$institutions),
            'qualification' => $this->faker->randomElement(['Bachelor of Arts', 'Bachelor of Science', 'WASSCE Certificate', 'Higher National Diploma']),
            'year_obtained' => (string) $this->faker->numberBetween(2015, 2023),
            'height' => $this->faker->randomFloat(2, 1.60, 1.85),
            'weight' => $this->faker->randomFloat(2, 55, 95),
            'criminal_record' => $criminalRecord,
            'fitness_status' => $fitnessStatus ?? $this->faker->randomElement(['fit', 'unfit']),
            'status' => $status,
            'submitted_at' => $withSubmittedAt ? now()->subDays($this->faker->numberBetween(0, 60)) : null,
        ];

        return Application::create($data);
    }

    private function createDocuments(Application $application): void
    {
        $types = ['birth_certificate', 'educational_certificate', 'national_id', 'passport_photograph'];

        foreach ($types as $type) {
            Document::create([
                'application_id' => $application->id,
                'document_type' => $type,
                'file_name' => 'doc_' . Str::random(10) . '.pdf',
                'file_path' => 'documents/' . date('Y/m/d') . '/' . Str::random(20),
                'file_size' => $this->faker->numberBetween(100000, 2000000),
                'mime_type' => 'application/pdf',
                'upload_date' => now()->subDays($this->faker->numberBetween(0, 30)),
                'verification_status' => 'pending',
                'ai_verified' => false,
            ]);
        }
    }

    private function uniqueSuffix(): string
    {
        static $counter = 0;
        return '_' . (++$counter);
    }
}
