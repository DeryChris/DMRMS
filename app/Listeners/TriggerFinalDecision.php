<?php

namespace App\Listeners;

use App\Jobs\ProcessFinalDecision;
use Illuminate\Support\Facades\Log;

class TriggerFinalDecision
{
    public function handle(): void
    {
        try {
            ProcessFinalDecision::dispatchSync();
        } catch (\Exception $e) {
            Log::error('Failed to dispatch final decision job: ' . $e->getMessage());
        }
    }
}
