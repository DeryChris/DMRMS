<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\FinalDecision;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoRecruit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Application $application;
    public int $tries = 3;
    public int $backoff = 120;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function handle(NotificationService $notifier): void
    {
        try {
            $app = $this->application->fresh();

            if (!$app || $app->status !== 'selected') {
                Log::info('AutoRecruit skipped: application not in selected status', [
                    'app_id' => $this->application->id,
                    'status' => $app?->status,
                ]);
                return;
            }

            $battalion = config('recruitment.auto_recruit.default_training_battalion', 'GAF Training Depot');

            $app->update([
                'status' => 'recruited',
                'enrolled_at' => Carbon::now(),
                'training_battalion' => $battalion,
            ]);

            $finalDecision = $app->finalDecision;
            if ($finalDecision) {
                $finalDecision->update([
                    'decision' => 'recruited',
                    'notes' => ($finalDecision->notes ?? '') . ' | Auto-recruited on ' . Carbon::now()->toDateString(),
                ]);
            }

            try {
                $notifier->finalDecision($app);
            } catch (\Exception $e) {
                Log::warning('AutoRecruit notification failed', [
                    'app_id' => $app->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('AutoRecruit completed', [
                'app_id' => $app->id,
                'gaf_id' => $app->gaf_id,
                'battalion' => $battalion,
            ]);

        } catch (\Exception $e) {
            Log::error('AutoRecruit job failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
