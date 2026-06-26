<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Services\Eligibility\EligibilityService;
use Illuminate\Console\Command;

class BackfillEligibility extends Command
{
    protected $signature = 'dmrms:backfill-eligibility {--id= : Run for a specific application ID} {--show : Show existing result instead of running}';
    protected $description = 'Run eligibility for existing submitted/draft applications that were created before auto-eligibility was implemented';

    public function handle(EligibilityService $eligibilityService): void
    {
        $showOnly = $this->option('show');
        $id = $this->option('id');

        if ($showOnly && $id) {
            $app = Application::with('eligibilityResult')->find($id);
            if (!$app) {
                $this->error("Application #{$id} not found.");
                return;
            }
            $r = $app->eligibilityResult;
            if (!$r) {
                $this->warn("No eligibility result for App #{$id}.");
                return;
            }
            $this->info("App #{$id} — Status: {$app->status}");
            $this->info("Overall: {$r->overall_status}");
            $this->line("  Age: " . ($r->age_check ? 'PASS' : 'FAIL'));
            $this->line("  Nationality: " . ($r->nationality_check ? 'PASS' : 'FAIL'));
            $this->line("  Education: " . ($r->education_check ? 'PASS' : 'FAIL'));
            $this->line("  Height: " . ($r->height_check ? 'PASS' : 'FAIL'));
            $this->line("  Marital: " . ($r->marital_check ? 'PASS' : 'FAIL'));
            $this->line("  Criminal: " . ($r->criminal_check ? 'PASS' : 'FAIL'));
            $this->line("  Documents: " . ($r->document_check ? 'PASS' : 'FAIL'));
            if (!empty($r->rejection_reasons)) {
                $this->warn('  Reasons: ' . implode('; ', (array) $r->rejection_reasons));
            }
            return;
        }

        $query = Application::whereIn('status', ['submitted', 'documents_verified', 'registered', 'draft'])
            ->whereDoesntHave('eligibilityResult');

        if ($id) {
            $query->where('id', $id);
        }

        $apps = $query->get();
        $count = $apps->count();

        if ($count === 0) {
            $this->info('No applications found to backfill.');
            return;
        }

        $this->info("Found {$count} application(s) to evaluate.");

        foreach ($apps as $app) {
            if (in_array($app->status, ['draft', 'registered'])) {
                $this->warn("Skipping App #{$app->id} — status is '{$app->status}', not yet submitted.");
                continue;
            }

            try {
                $result = $eligibilityService->evaluate($app);
                $status = $result['status'];
                $this->info("App #{$app->id}: {$status}");

                if ($this->option('verbose')) {
                    foreach ($result['checks'] as $key => $check) {
                        $icon = $check['passed'] ? 'PASS' : 'FAIL';
                        $this->line("  [{$icon}] {$check['criterion']}");
                    }
                    if (!empty($result['rejection_reasons'])) {
                        $this->warn('  Reasons: ' . implode('; ', $result['rejection_reasons']));
                    }
                }
            } catch (\Throwable $e) {
                $this->error("App #{$app->id}: Error — {$e->getMessage()}");
            }
        }

        $this->info('Done.');
    }
}
