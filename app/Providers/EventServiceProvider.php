<?php

namespace App\Providers;

use App\Events\ApplicationSubmitted;
use App\Events\DocumentUploaded;
use App\Events\EligibilityPassed;
use App\Events\ScreeningCompleted;
use App\Listeners\DispatchDocumentVerification;
use App\Listeners\TriggerAutoShortlist;
use App\Listeners\TriggerAutoScheduling;
use App\Listeners\TriggerFinalDecision;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DocumentUploaded::class => [
            DispatchDocumentVerification::class,
        ],
        ApplicationSubmitted::class => [
            DispatchDocumentVerification::class,
        ],
        EligibilityPassed::class => [
            TriggerAutoShortlist::class,
        ],
        ScreeningCompleted::class => [
            TriggerFinalDecision::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
