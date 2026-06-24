<?php

namespace Database\Seeders;

use App\Models\Cycle;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $activeCycles = Cycle::where('status', 'active')->get();

        foreach ($activeCycles as $cycle) {
            for ($i = 0; $i < 50; $i++) {
                Voucher::create([
                    'cycle_id' => $cycle->id,
                    'serial_number' => 'DMRMS-' . strtoupper(Str::random(8)),
                    'pin_code' => Str::random(10),
                    'status' => 'available',
                    'expires_at' => now()->addYear(),
                ]);
            }
        }
    }
}
