<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministratorSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'first_name' => 'General',
            'last_name' => 'Admin',
            'email' => 'admin@dmrms.gov.gh',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'subscription_tier' => 'enterprise',
            'status' => 'active',
        ]);
        $admin->assignRole('super_admin');

        $recruitment = User::create([
            'first_name' => 'Recruitment',
            'last_name' => 'Officer',
            'email' => 'recruitment@dmrms.gov.gh',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'subscription_tier' => 'enterprise',
            'status' => 'active',
        ]);
        $recruitment->assignRole('recruitment_officer');

        $screening = User::create([
            'first_name' => 'Screening',
            'last_name' => 'Officer',
            'email' => 'screening@dmrms.gov.gh',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'subscription_tier' => 'enterprise',
            'status' => 'active',
        ]);
        $screening->assignRole('screening_officer');
    }
}
