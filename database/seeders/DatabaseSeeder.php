<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CycleSeeder::class,
            AdministratorSeeder::class,
            VoucherSeeder::class,
            ApplicantSeeder::class,
            AnnouncementSeeder::class,
            FaqSeeder::class,
            BarrackSeeder::class,
            GafStructureSeeder::class,
        ]);
    }
}
