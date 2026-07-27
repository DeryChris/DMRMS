<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Applicant;
use App\Models\Notification;
use App\Services\Notification\NotificationService;
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
        $dashboardCount = 0;
        $smsCount = 0;

        if ($validated['target_type'] === 'applicants') {
            $query = Applicant::query();

            $regions = $validated['regions'] ?? [];
            if (!empty($regions) && !in_array('', $regions, true)) {
                $query->whereIn('region', $regions);
            }

            $applicants = $query->get();

            foreach ($applicants as $applicant) {
                // Dashboard notification (fast — DB insert)
                Notification::create([
                    'applicant_id' => $applicant->id,
                    'type' => 'admin_broadcast',
                    'subject' => $subject,
                    'message' => $messageBody,
                    'channel' => 'dashboard',
                    'sent_at' => now(),
                ]);
                $dashboardCount++;

                // SMS notification (fast — HTTPS API)
                try {
                    app(NotificationService::class)->sendSms($applicant->contact_number, $messageBody);
                    $smsCount++;
                } catch (\Throwable $e) {
                    Log::warning("Admin broadcast SMS failed to {$applicant->contact_number}: " . $e->getMessage());
                }

                // Email notification (queued — avoids blocking HTTP response)
                \App\Jobs\SendAdminBroadcastEmail::dispatch($applicant, $subject, $messageBody);

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
                $dashboardCount++;
                $count++;
            }
        }

        $successMsg = "Notification sent! Dashboard: {$dashboardCount} · SMS: {$smsCount} · Emails queued: {$count}";

        return redirect()->route('admin.notifications.create')
            ->with('success', $successMsg);
    }
}
