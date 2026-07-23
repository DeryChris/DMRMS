<?php

namespace App\Console\Commands;

use App\Services\Application\CorpAllocationService;
use Illuminate\Console\Command;

class CorpsAllocateCommand extends Command
{
    protected $signature = 'corps:allocate {--cycle= : The cycle ID to allocate for (defaults to active cycle)}';
    protected $description = 'Allocate shortlisted applicants to corps based on preferences and eligibility';

    public function handle(CorpAllocationService $allocationService): int
    {
        $cycleId = $this->option('cycle');

        if (!$cycleId) {
            $cycle = \App\Models\Cycle::where('status', 'active')->first();
            if (!$cycle) {
                $this->error('No active cycle found. Specify a cycle ID with --cycle.');
                return Command::FAILURE;
            }
            $cycleId = $cycle->id;
        }

        $this->info("Starting corps allocation for cycle #{$cycleId}...");

        $stats = $allocationService->getAllocationStats($cycleId);
        $this->line("Pending: {$stats['pending']}, Already allocated: {$stats['allocated']}, Unallocated: {$stats['unallocated']}");

        if ($stats['pending'] === 0) {
            $this->info('No pending applications to allocate.');
            return Command::SUCCESS;
        }

        $result = $allocationService->allocate($cycleId);

        $this->info("Allocation complete: {$result['allocated']} allocated, {$result['unallocated']} unallocated, {$result['skipped']} skipped.");

        return Command::SUCCESS;
    }
}
