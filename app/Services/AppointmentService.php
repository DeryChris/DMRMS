<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Application;
use App\Models\VerificationCode;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function createSlots($cycleId, array $slots): array
    {
        $created = [];

        DB::transaction(function () use ($cycleId, $slots, &$created) {
            foreach ($slots as $index => $slot) {
                $appointment = Appointment::create([
                    'application_id'  => null,
                    'scheduled_date'  => $slot['date'],
                    'scheduled_time'  => $slot['time'],
                    'venue'           => $slot['venue'],
                    'slot_number'     => $slot['capacity'] ?? 1,
                    'status'          => 'available',
                ]);
                $created[] = $appointment;
            }
        });

        return $created;
    }

    public function autoAllocateSlots($cycleId): array
    {
        $shortlisted = Application::where('cycle_id', $cycleId)
            ->where('status', 'shortlisted')
            ->whereDoesntHave('appointment')
            ->get();

        $slots = Appointment::whereNull('application_id')
            ->whereNull('checked_in_at')
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        if ($slots->isEmpty()) {
            return [];
        }

        $allocations = [];
        $slotIndex = 0;
        $perSlot = (int) ceil($shortlisted->count() / $slots->count());

        DB::transaction(function () use ($shortlisted, $slots, $perSlot, &$slotIndex, &$allocations) {
            foreach ($shortlisted as $app) {
                $slot = $slots[$slotIndex];

                $slot->update([
                    'application_id' => $app->id,
                    'status'         => 'scheduled',
                ]);

                $allocations[] = [
                    'application_id'  => $app->id,
                    'appointment_id'  => $slot->id,
                    'scheduled_date'  => $slot->scheduled_date,
                    'scheduled_time'  => $slot->scheduled_time,
                    'venue'           => $slot->venue,
                ];

                $this->notificationService->appointmentScheduled($app, $slot);

                if (($slotIndex + 1) < $slots->count() && $allocations % $perSlot === 0) {
                    $slotIndex++;
                }
            }
        });

        return $allocations;
    }

    public function checkConflicts($date, $time, $venue): array
    {
        $conflicts = Appointment::where('scheduled_date', $date)
            ->where('scheduled_time', $time)
            ->where('venue', $venue)
            ->whereNull('checked_in_at')
            ->get();

        return [
            'has_conflict'  => $conflicts->isNotEmpty(),
            'conflicts'     => $conflicts,
        ];
    }

    public function reschedule($appointmentId, $newSlotData): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        $conflictCheck = $this->checkConflicts(
            $newSlotData['date'],
            $newSlotData['time'],
            $newSlotData['venue']
        );

        if ($conflictCheck['has_conflict']) {
            throw new \RuntimeException('The requested slot conflicts with an existing appointment.');
        }

        $appointment->update([
            'scheduled_date' => $newSlotData['date'],
            'scheduled_time' => $newSlotData['time'],
            'venue'          => $newSlotData['venue'],
            'status'         => 'rescheduled',
        ]);

        $application = $appointment->application;
        if ($application) {
            $this->notificationService->appointmentScheduled($application, $appointment);
        }

        return $appointment->fresh();
    }

    public function markAttendance($verificationCode): array
    {
        $code = VerificationCode::where('code_value', $verificationCode)
            ->where('used_status', false)
            ->where('expiry_date', '>=', Carbon::now())
            ->first();

        if (!$code) {
            return ['success' => false, 'error' => 'Invalid or expired verification code.'];
        }

        $appointment = Appointment::where('application_id', $code->application_id)
            ->whereNull('checked_in_at')
            ->first();

        if (!$appointment) {
            return ['success' => false, 'error' => 'No pending appointment found for this code.'];
        }

        $appointment->update([
            'checked_in_at' => Carbon::now(),
            'status'        => 'checked_in',
        ]);

        $code->update([
            'used_status' => true,
            'used_at'     => Carbon::now(),
        ]);

        $application = $appointment->application()->with('applicant')->first();

        return [
            'success'     => true,
            'applicant'   => $application->applicant ?? null,
            'appointment' => $appointment,
            'application' => $application,
        ];
    }
}
