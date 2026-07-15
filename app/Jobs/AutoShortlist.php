<?php

namespace App\Jobs;

use App\Models\Application;
use App\Services\ShortlistingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoShortlist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function handle(ShortlistingService $shortlistingService): void
    {
        try {
            $applications = Application::where('status', 'eligibility_passed')
                ->whereDoesntHave('screeningResult')
                ->get();

            if ($applications->isEmpty()) {
                Log::info('AutoShortlist: No eligible applicants to shortlist');
                return;
            }

            $applicationIds = $applications->pluck('id')->toArray();

            $shortlistingService->bulkShortlist($applicationIds, null);

            $count = count($applicationIds);
            Log::info("AutoShortlist: Successfully shortlisted {$count} applicants");

            AutoScheduleAppointments::dispatch();

        } catch (\Exception $e) {
            Log::error('AutoShortlist job failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
