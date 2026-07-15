<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFinalDecision;
use Illuminate\Console\Command;

class ProcessFinalDecisionCommand extends Command
{
    protected $signature = 'decision:auto {--sync : Run synchronously instead of dispatching to queue}';
    protected $description = 'Auto-process final decisions for screened applicants';

    public function handle(): int
    {
        $this->info('Processing final decisions...');

        if ($this->option('sync')) {
            ProcessFinalDecision::dispatchSync();
            $this->info('Final decisions processed synchronously.');
        } else {
            ProcessFinalDecision::dispatch();
            $this->info('Final decision job dispatched to queue.');
        }

        return Command::SUCCESS;
    }
}
