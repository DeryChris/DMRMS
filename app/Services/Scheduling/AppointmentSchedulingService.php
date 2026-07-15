<?php

namespace App\Services\Scheduling;

use App\Models\Application;
use App\Models\Appointment;
use App\Models\Barrack;
use App\Models\VerificationCode;
use App\Services\Notification\NotificationService;
use App\Services\ShortlistingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentSchedulingService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected ShortlistingService $shortlistingService,
    ) {}

    public function createSlots(string $date, string $time, int $capacity): array
    {
        $slots = [];

        DB::transaction(function () use ($date, $time, $capacity, &$slots) {
            $applicants = Application::with('applicant')
                ->where('status', 'shortlisted')
                ->inRandomOrder()
                ->take($capacity)
                ->get();

            foreach ($applicants as $i => $app) {
                $venue = $this->resolveVenue($app);

                $appointment = Appointment::create([
                    'application_id' => $app->id,
                    'scheduled_date' => $date,
                    'scheduled_time' => $time,
                    'venue'          => $venue,
                    'slot_number'    => $i + 1,
                    'status'         => 'scheduled',
                ]);

                $app->update(['status' => 'appointment_scheduled']);

                $verificationCode = $this->createVerificationCode($app);

                $this->notificationService->appointmentScheduled($app, $appointment, $verificationCode);

                $slots[] = $appointment;
            }
        });

        return $slots;
    }

    public function assignSingle(Application $app, string $date, string $time, int $slotNumber): Appointment
    {
        $appointment = DB::transaction(function () use ($app, $date, $time, $slotNumber) {
            $venue = $this->resolveVenue($app);

            $appointment = Appointment::create([
                'application_id' => $app->id,
                'scheduled_date' => $date,
                'scheduled_time' => $time,
                'venue'          => $venue,
                'slot_number'    => $slotNumber,
                'status'         => 'scheduled',
            ]);

            $app->update(['status' => 'appointment_scheduled']);

            $verificationCode = $this->createVerificationCode($app);

            $this->notificationService->appointmentScheduled($app, $appointment, $verificationCode);

            return $appointment;
        });

        return $appointment;
    }

    private function resolveVenue(Application $app): string
    {
        $applicant = $app->applicant;

        if ($applicant?->region) {
            $barrack = Barrack::where('region', $applicant->region)
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();

            if ($barrack) {
                return $barrack->name . ($barrack->location ? ', ' . $barrack->location : '');
            }
        }

        return 'GAF Training Camp';
    }

    private function createVerificationCode(Application $app): VerificationCode
    {
        $codeValue = strtoupper(Str::random(12));

        $qrPath = $this->shortlistingService->generateQrCode($codeValue);

        return VerificationCode::create([
            'application_id' => $app->id,
            'applicant_id' => $app->applicant_id,
            'code_value' => $codeValue,
            'type' => 'entry',
            'qr_code_path' => $qrPath,
            'issue_date' => Carbon::now(),
            'expiry_date' => Carbon::now()->addMonths(6),
            'used_status' => false,
        ]);
    }

    public function getAvailableSlots(string $date): int
    {
        $totalShortlisted = Application::where('status', 'shortlisted')->count();
        $alreadyScheduled = Appointment::whereDate('scheduled_date', $date)->count();

        return max(0, $totalShortlisted - $alreadyScheduled);
    }
}
