<?php

namespace Database\Seeders;

use App\Models\Administrator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministratorSeeder extends Seeder
{
    public function run(): void
    {
        Administrator::create([
            'first_name' => 'General',
            'last_name' => 'Admin',
            'email' => 'admin@dmrms.gov.gh',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'subscription_tier' => 'enterprise',
            'status' => 'active',
        ]);

        Administrator::create([
            'first_name' => 'Recruitment',
            'last_name' => 'Officer',
            'email' => 'recruitment@dmrms.gov.gh',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'subscription_tier' => 'enterprise',
            'status' => 'active',
        ]);

        Administrator::create([
            'first_name' => 'Screening',
            'last_name' => 'Officer',
            'email' => 'screening@dmrms.gov.gh',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'subscription_tier' => 'enterprise',
            'status' => 'active',
        ]);
    }
}
