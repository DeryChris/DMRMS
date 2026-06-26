<?php

namespace App\Services\Scheduling;

use App\Models\Application;
use App\Models\Appointment;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentSchedulingService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function createSlots(string $date, string $time, string $venue, int $capacity): array
    {
        $slots = [];

        DB::transaction(function () use ($date, $time, $venue, $capacity, &$slots) {
            $applicants = Application::where('status', 'shortlisted')
                ->inRandomOrder()
                ->take($capacity)
                ->get();

            foreach ($applicants as $i => $app) {
                $appointment = Appointment::create([
                    'application_id' => $app->id,
                    'scheduled_date' => $date,
                    'scheduled_time' => $time,
                    'venue'          => $venue,
                    'slot_number'    => $i + 1,
                    'status'         => 'scheduled',
                ]);

                $app->update(['status' => 'appointment_scheduled']);

                $this->notificationService->appointmentScheduled($app, $appointment);

                $slots[] = $appointment;
            }
        });

        return $slots;
    }

    public function assignSingle(Application $app, string $date, string $time, string $venue, int $slotNumber): Appointment
    {
        $appointment = DB::transaction(function () use ($app, $date, $time, $venue, $slotNumber) {
            $appointment = Appointment::create([
                'application_id' => $app->id,
                'scheduled_date' => $date,
                'scheduled_time' => $time,
                'venue'          => $venue,
                'slot_number'    => $slotNumber,
                'status'         => 'scheduled',
            ]);

            $app->update(['status' => 'appointment_scheduled']);

            $this->notificationService->appointmentScheduled($app, $appointment);

            return $appointment;
        });

        return $appointment;
    }

    public function getAvailableSlots(string $date): int
    {
        $totalShortlisted = Application::where('status', 'shortlisted')->count();
        $alreadyScheduled = Appointment::whereDate('scheduled_date', $date)->count();

        return max(0, $totalShortlisted - $alreadyScheduled);
    }
}
