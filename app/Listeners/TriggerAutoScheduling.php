<?php

namespace App\Listeners;

use App\Jobs\AutoScheduleAppointments;
use Illuminate\Support\Facades\Log;

class TriggerAutoScheduling
{
    public function handle(): void
    {
        try {
            AutoScheduleAppointments::dispatch();
        } catch (\Exception $e) {
            Log::error('Failed to dispatch auto scheduling job: ' . $e->getMessage());
        }
    }
}
