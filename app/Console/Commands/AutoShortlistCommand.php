<?php

namespace App\Console\Commands;

use App\Jobs\AutoShortlist;
use Illuminate\Console\Command;

class AutoShortlistCommand extends Command
{
    protected $signature = 'shortlist:auto';
    protected $description = 'Auto-shortlist all eligible applicants';

    public function handle(): int
    {
        $this->info('Dispatching auto shortlist job...');

        AutoShortlist::dispatch();

        $this->info('Auto shortlist job dispatched to queue.');

        return Command::SUCCESS;
    }
}
