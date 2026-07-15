<?php

namespace App\Listeners;

use App\Events\EligibilityPassed;
use App\Jobs\AutoShortlist;

class TriggerAutoShortlist
{
    public function handle(EligibilityPassed $event): void
    {
        AutoShortlist::dispatch();
    }
}
