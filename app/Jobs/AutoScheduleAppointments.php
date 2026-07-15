<?php

namespace App\Jobs;

use App\Models\Application;
use App\Services\Scheduling\AppointmentSchedulingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoScheduleAppointments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function handle(AppointmentSchedulingService $schedulingService): void
    {
        try {
            $shortlisted = Application::where('status', 'shortlisted')
                ->whereDoesntHave('appointment')
                ->get();

            if ($shortlisted->isEmpty()) {
                Log::info('AutoSchedule: No shortlisted applicants awaiting scheduling');
                return;
            }

            $config = config('recruitment.scheduling', [
                'default_date' => Carbon::tomorrow()->format('Y-m-d'),
                'default_time' => '08:00',
                'daily_capacity' => 50,
            ]);

            $slotDate = $config['default_date'];
            if ($slotDate === 'next_weekday') {
                $slotDate = Carbon::tomorrow();
                while ($slotDate->isWeekend()) {
                    $slotDate->addDay();
                }
                $slotDate = $slotDate->format('Y-m-d');
            }

            $results = $schedulingService->createSlots(
                date: $slotDate,
                time: $config['default_time'],
                capacity: min($shortlisted->count(), $config['daily_capacity'])
            );

            $count = count($results);
            Log::info("AutoSchedule: Assigned {$count} applicants to appointments");

        } catch (\Exception $e) {
            Log::error('AutoScheduleAppointments job failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
