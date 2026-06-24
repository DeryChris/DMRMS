<?php

namespace Database\Factories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    protected static ?string $password;

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
        'Nalerigu', 'Damongo', 'Dambai', 'Nkwanta', 'Hohoe',
        'Mampong', 'Ejura', 'Agona', 'Awutu Senya', 'Ga South',
        'Adenta', 'Madina', 'La Nkwantanang', 'Ledzokuku', 'Krowor',
    ];

    private static array $ghanaianFirstNames = [
        'Kwame', 'Yaa', 'Kofi', 'Ama', 'Yaw', 'Esi', 'Kwesi', 'Abena',
        'Kweku', 'Akosua', 'Kwabena', 'Adwoa', 'Nana', 'Akua', 'Kojo', 'Yaa',
        'Samuel', 'Grace', 'Emmanuel', 'Mary', 'Michael', 'Patricia', 'David',
        'Elizabeth', 'Daniel', 'Catherine', 'Joseph', 'Sarah', 'John', 'Esther',
        'Peter', 'Martha', 'George', 'Beatrice', 'Eric', 'Joyce', 'Isaac', 'Ruth',
        'Solomon', 'Alice', 'Richard', 'Janet', 'William', 'Florence', 'James', 'Rose',
    ];

    private static array $ghanaianLastNames = [
        'Mensah', 'Agyapong', 'Ansah', 'Osei', 'Owusu', 'Sarpong', 'Boateng',
        'Asante', 'Appiah', 'Yeboah', 'Adjei', 'Antwi', 'Agyeman', 'Tetteh',
        'Darko', 'Opoku', 'Afriyie', 'Acheampong', 'Boadu', 'Dankwah',
        'Armah', 'Sowah', 'Quartey', 'Bannerman', 'Ackon', 'Cudjoe', 'Nortey',
        'Tawiah', 'Sakyi', 'Djan', 'Amoako', 'Ababio', 'Ofori', 'Mireku',
    ];

    public function definition(): array
    {
        $firstName = fake()->randomElement(self::$ghanaianFirstNames);
        $lastName = fake()->randomElement(self::$ghanaianLastNames);
        $gender = fake()->randomElement(['Male', 'Female']);
        $region = fake()->randomElement(self::$regions);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'other_names' => fake()->optional(0.5)->firstName(),
            'date_of_birth' => fake()->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'gender' => $gender,
            'marital_status' => fake()->randomElement(['Single', 'Single', 'Single', 'Single', 'Married']),
            'contact_number' => '+233' . fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'residential_address' => 'P.O. Box ' . fake()->numberBetween(100, 9999) . ', ' . $region,
            'region' => $region,
            'district' => fake()->randomElement(self::$districts),
            'nationality' => 'Ghanaian',
            'national_id' => 'GHA-' . fake()->unique()->numerify('##########'),
            'password' => static::$password ??= Hash::make('password123'),
            'email_verified_at' => now(),
            'status' => 'active',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'Male',
        ]);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'Female',
        ]);
    }
}
