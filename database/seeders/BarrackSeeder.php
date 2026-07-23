<?php

namespace Database\Seeders;

use App\Models\Barrack;
use Illuminate\Database\Seeder;

class BarrackSeeder extends Seeder
{
    private array $data = [
        'Greater Accra' => [
            ['name' => 'Burma Camp', 'location' => 'Accra'],
            ['name' => 'Michel Camp', 'location' => 'Tema'],
        ],
        'Ashanti' => [
            ['name' => 'Uaddara Barracks', 'location' => 'Kumasi'],
            ['name' => 'Gondar Barracks', 'location' => 'Kumasi'],
        ],
        'Northern' => [
            ['name' => 'Kamina Barracks', 'location' => 'Tamale'],
        ],
        'Volta' => [
            ['name' => 'Volta Barracks', 'location' => 'Ho'],
        ],
        'Western' => [
            ['name' => 'Fort George', 'location' => 'Sekondi-Takoradi'],
        ],
        'Upper East' => [
            ['name' => 'Basoa Camp', 'location' => 'Bolgatanga'],
        ],
        'Upper West' => [
            ['name' => 'Wa Airborne Force Base', 'location' => 'Wa'],
        ],
        'Central' => [
            ['name' => 'AFCA Auxiliary Training School', 'location' => 'Kintampo'],
        ],
        'Eastern' => [
            ['name' => 'Shai Hills Camp', 'location' => 'Shai Hills'],
            ['name' => 'Asutuare Camp', 'location' => 'Asutuare'],
        ],
        'Bono' => [
            ['name' => 'Sunyani Military Barracks', 'location' => 'Sunyani'],
        ],
        'Bono East' => [
            ['name' => 'Techiman Military Camp', 'location' => 'Techiman'],
        ],
        'North East' => [
            ['name' => 'Nalerigu Military Post', 'location' => 'Nalerigu'],
        ],
        'Oti' => [
            ['name' => 'Dambai Military Post', 'location' => 'Dambai'],
        ],
        'Savannah' => [
            ['name' => 'Damongo Military Post', 'location' => 'Damongo'],
        ],
        'Western North' => [
            ['name' => 'Sefwi Wiawso Military Post', 'location' => 'Sefwi Wiawso'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->data as $region => $barracks) {
            foreach ($barracks as $barrack) {
                Barrack::create([
                    'region' => $region,
                    'name' => $barrack['name'],
                    'location' => $barrack['location'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
