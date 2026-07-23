<?php

namespace App\Services\Voucher;

use App\Models\Voucher;
use App\Models\Cycle;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function generate($cycleId, $count = 1): array
    {
        $cycle = Cycle::findOrFail($cycleId);

        $vouchers = [];
        $now = Carbon::now();
        $expiresAt = $cycle->application_deadline ?? $now->copy()->addMonths(3);

        DB::transaction(function () use ($cycleId, $count, $now, $expiresAt, &$vouchers) {
            for ($i = 0; $i < $count; $i++) {
                $voucher = Voucher::create([
                    'cycle_id'    => $cycleId,
                    'serial_number' => $this->generateUniqueSerial(),
                    'pin_code'      => $this->generatePin(),
                    'purchased_at'  => $now,
                    'status'        => 'available',
                    'expires_at'    => $expiresAt,
                ]);
                $vouchers[] = $voucher;
            }
        });

        return $vouchers;
    }

    public function validate($serialNumber, $pinCode): array
    {
        $voucher = Voucher::where('serial_number', $serialNumber)->first();

        if (!$voucher) {
            return ['valid' => false, 'error' => 'Voucher not found.'];
        }

        if ($voucher->status !== 'available') {
            return ['valid' => false, 'error' => "Voucher is already {$voucher->status}."];
        }

        if ($voucher->pin_code !== $pinCode) {
            return ['valid' => false, 'error' => 'Invalid PIN code.'];
        }

        if ($voucher->expires_at && Carbon::now()->gt($voucher->expires_at)) {
            return ['valid' => false, 'error' => 'Voucher has expired.'];
        }

        return ['valid' => true, 'voucher' => $voucher];
    }

    public function markAsUsed($voucherId, $applicantId): bool
    {
        return Voucher::where('id', $voucherId)
            ->where('status', 'available')
            ->update([
                'status'   => 'used',
                'used_by'  => $applicantId,
                'used_at'  => Carbon::now(),
            ]) > 0;
    }

    public function getStatus($voucherId): string
    {
        $voucher = Voucher::find($voucherId);

        return $voucher ? $voucher->status : 'not_found';
    }

    public function getAvailableCount($cycleId): int
    {
        return Voucher::where('cycle_id', $cycleId)
            ->where('status', 'available')
            ->count();
    }

    private function generateUniqueSerial(): string
    {
        do {
            $serial = 'DMRMS-' . strtoupper(Str::random(8));
        } while (Voucher::where('serial_number', $serial)->exists());

        return $serial;
    }

    private function generatePin(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($chars, 10)), 0, 10);
    }
}
