<?php

namespace Database\Factories;

use App\Models\Cycle;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    public function definition(): array
    {
        return [
            'cycle_id' => Cycle::factory(),
            'serial_number' => 'DMRMS-' . strtoupper(Str::random(8)),
            'pin_code' => Str::random(10),
            'status' => 'available',
            'expires_at' => now()->addYear(),
        ];
    }
}
