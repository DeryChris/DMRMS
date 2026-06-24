<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireVouchers extends Command
{
    protected $signature = 'vouchers:expire';

    protected $description = 'Expire vouchers past their expiry date';

    public function handle(): int
    {
        $count = Voucher::where('expires_at', '<', Carbon::now())
            ->where('status', 'available')
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} voucher(s)");

        return Command::SUCCESS;
    }
}
