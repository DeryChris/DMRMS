<?php

namespace App\Providers;

use App\Events\ApplicationSubmitted;
use App\Events\DocumentUploaded;
use App\Events\EligibilityPassed;
use App\Events\ScreeningCompleted;
use App\Listeners\DispatchDocumentVerification;
use App\Listeners\DispatchCrossVerification;
use App\Listeners\TriggerAutoShortlist;
use App\Listeners\TriggerAutoScheduling;
use App\Listeners\TriggerFinalDecision;
use App\Models\Document;
use App\Observers\DocumentObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DocumentUploaded::class => [
            DispatchDocumentVerification::class,
            DispatchCrossVerification::class,
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

        // Universal safety net: catches disqualifying document rejections
        // from any code path (AI job, admin controller, API, etc.)
        Document::observe(DocumentObserver::class);
    }
}
