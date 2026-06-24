<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendScreeningReminders extends Command
{
    protected $signature = 'reminders:screening';

    protected $description = 'Send screening reminders for tomorrow appointments';

    public function handle(NotificationService $notificationService): int
    {
        $appointments = Appointment::with('application.applicant')
            ->whereDate('scheduled_date', Carbon::tomorrow())
            ->where('notification_sent', false)
            ->get();

        $count = 0;

        foreach ($appointments as $appointment) {
            $application = $appointment->application;

            if ($application && $application->applicant) {
                $notificationService->screeningReminder($application, $appointment);
                $appointment->update(['notification_sent' => true]);
                $count++;
            }
        }

        $this->info("Sent {$count} screening reminder(s)");

        return Command::SUCCESS;
    }
}
