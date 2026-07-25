<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\EligibilityResult;
use App\Services\Eligibility\EligibilityEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEligibilityBatch extends Command
{
    protected $signature = 'eligibility:process';

    protected $description = 'Process eligibility for submitted applications';

    public function handle(EligibilityEngine $engine): int
    {
        $applications = Application::where('status', 'submitted')
            ->whereDoesntHave('eligibilityResult')
            ->get();

        $count = 0;

        foreach ($applications as $app) {
            $result = $engine->evaluate($app);

            EligibilityResult::create([
                'application_id' => $app->id,
                'age_check' => $result['checks']['age']['passed'],
                'nationality_check' => $result['checks']['nationality']['passed'],
                'education_check' => $result['checks']['education']['passed'],
                'height_check' => $result['checks']['height']['passed'],
                'criminal_check' => $result['checks']['criminal_record']['passed'],
                'document_check' => $result['checks']['documents']['passed'],
                'marital_check' => $result['checks']['marital_status']['passed'],
                'overall_status' => $result['overall_status'],
                'rejection_reasons' => $result['rejection_reasons'],
                'evaluation_date' => now(),
            ]);

            $newStatus = $result['overall_status'] === 'eligible' ? 'eligibility_passed' : 'eligibility_failed';
            $app->update(['status' => $newStatus]);

            Log::info('Eligibility processed via batch command', [
                'application_id' => $app->id,
                'result' => $result['overall_status'],
                'new_status' => $newStatus,
            ]);

            $count++;
        }

        $this->info("Processed {$count} application(s)");

        return Command::SUCCESS;
    }
}
