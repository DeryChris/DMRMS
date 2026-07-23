<?php

namespace App\Console\Commands;

use App\Models\Cycle;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CycleAutoDeactivate extends Command
{
    protected $signature = 'cycles:auto-deactivate';

    protected $description = 'Auto-deactivate cycles past their end date';

    public function handle(): int
    {
        $count = Cycle::where('status', 'active')
            ->where('end_date', '<', Carbon::now())
            ->update(['status' => 'closed']);

        $this->info("Deactivated {$count} cycle(s) past their end date.");

        return Command::SUCCESS;
    }
}
