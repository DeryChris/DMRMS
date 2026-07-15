<?php

namespace App\Console\Commands;

use App\Jobs\AutoScheduleAppointments;
use Illuminate\Console\Command;

class AutoScheduleCommand extends Command
{
    protected $signature = 'schedule:auto';
    protected $description = 'Auto-assign appointments to shortlisted applicants';

    public function handle(): int
    {
        $this->info('Dispatching auto scheduling job...');

        AutoScheduleAppointments::dispatch();

        $this->info('Auto scheduling job dispatched to queue.');

        return Command::SUCCESS;
    }
}
