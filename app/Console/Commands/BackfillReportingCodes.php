<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Barrack;
use Illuminate\Console\Command;

class BackfillReportingCodes extends Command
{
    protected $signature = 'app:backfill-reporting-codes';
    protected $description = 'Backfill missing reporting_code and barrack_id for existing selected/recruited applicants';

    public function handle()
    {
        $applications = Application::with('applicant', 'finalDecision')
            ->whereIn('status', ['selected', 'recruited'])
            ->get();

        if ($applications->isEmpty()) {
            $this->info('No selected/recruited applicants found.');
            return Command::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($applications as $application) {
            $finalDecision = $application->finalDecision;
            if (!$finalDecision) {
                $this->warn("Application #{$application->id} has no FinalDecision record, skipping.");
                $skipped++;
                continue;
            }

            if ($finalDecision->reporting_code) {
                $skipped++;
                continue;
            }

            $finalDecision->reporting_code = 'GAF-' . strtoupper(substr(uniqid(), -8));

            $applicant = $application->applicant;
            if ($applicant?->region && !$finalDecision->barrack_id) {
                $barrack = Barrack::where('region', $applicant->region)->where('is_active', true)->first();
                if ($barrack) {
                    $finalDecision->barrack_id = $barrack->id;
                }
            }

            $finalDecision->save();
            $updated++;
        }

        $this->info("Done. Updated: {$updated}, Skipped: {$skipped}");
        return Command::SUCCESS;
    }
}
