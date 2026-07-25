<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Applicant;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function create(): View
    {
        $roles = [
            'applicants' => 'All Applicants',
            'admins' => 'All Administrators',
        ];

        $adminRoles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'recruitment_officer' => 'Recruitment Officer',
            'screening_officer' => 'Screening Officer',
            'scheduling_officer' => 'Scheduling Officer',
        ];

        $regions = [
            'Ahafo', 'Ashanti', 'Bono', 'Bono East', 'Central', 'Eastern',
            'Greater Accra', 'Northern', 'North East', 'Oti', 'Savannah',
            'Upper East', 'Upper West', 'Volta', 'Western', 'Western North',
        ];

        return view('admin.notifications.send', compact('roles', 'adminRoles', 'regions'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_type' => ['required', 'in:applicants,admins'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'in:super_admin,admin,recruitment_officer,screening_officer,scheduling_officer'],
            'regions' => ['nullable', 'array'],
            'regions.*' => ['string'],
        ]);

        $subject = $validated['subject'];
        $messageBody = $validated['message'];
        $count = 0;
        $emailCount = 0;

        $fromAddress = config('mail.from.address', 'amoaheugene23@gmail.com');
        $fromName = config('mail.from.name', 'Ghana Armed Forces');

        if ($validated['target_type'] === 'applicants') {
            $query = Applicant::query();

            $regions = $validated['regions'] ?? [];
            if (!empty($regions) && !in_array('', $regions, true)) {
                $query->whereIn('region', $regions);
            }

            $applicants = $query->get();

            foreach ($applicants as $applicant) {
                // Dashboard notification
                Notification::create([
                    'applicant_id' => $applicant->id,
                    'type' => 'admin_broadcast',
                    'subject' => $subject,
                    'message' => $messageBody,
                    'channel' => 'dashboard',
                    'sent_at' => now(),
                ]);

                // Email notification
                try {
                    Mail::send('emails.admin-broadcast', [
                        'applicant' => $applicant,
                        'subject' => $subject,
                        'messageBody' => $messageBody,
                    ], function ($mail) use ($applicant, $subject, $fromAddress, $fromName) {
                        $mail->to($applicant->email, $applicant->name)
                             ->subject($subject)
                             ->from($fromAddress, $fromName);
                    });
                    $emailCount++;
                } catch (\Exception $e) {
                    Log::error("Admin broadcast email failed to {$applicant->email}: " . $e->getMessage());
                }

                $count++;
            }
        } else {
            $query = Administrator::query();

            $roles = $validated['roles'] ?? [];
            if (!empty($roles) && !in_array('', $roles, true)) {
                $query->whereIn('role', $roles);
            }

            $admins = $query->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'admin_id' => $admin->id,
                    'type' => 'admin_broadcast',
                    'subject' => $subject,
                    'message' => $messageBody,
                    'channel' => 'dashboard',
                    'sent_at' => now(),
                ]);
                $count++;
            }
        }

        $successMsg = "Notification sent to {$count} recipient(s).";
        if ($emailCount > 0) {
            $successMsg .= " Email delivered to {$emailCount} applicant(s).";
        }

        return redirect()->route('admin.notifications.create')
            ->with('success', $successMsg);
    }
}
