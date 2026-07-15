<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('vouchers:expire')->daily();
        $schedule->command('eligibility:process')->everyFifteenMinutes();
        $schedule->command('reminders:screening')->dailyAt('08:00');
        $schedule->command('audit:clean')->weeklyOn(0);

        $schedule->command('shortlist:auto')->everyFifteenMinutes();
        $schedule->command('schedule:auto')->everyFifteenMinutes()->between('6:00', '20:00');
        $schedule->command('decision:auto --sync')->dailyAt('02:00');
        $schedule->command('reserve:promote')->everyFifteenMinutes()->between('6:00', '20:00');
        $schedule->command('app:purge-soft-deleted')->dailyAt('03:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
