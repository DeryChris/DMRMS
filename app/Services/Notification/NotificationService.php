<?php

namespace App\Services\Notification;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Appointment;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send($recipient, $type, $data): void
    {
        $channel = match ($type) {
            'email'    => 'email',
            'sms'      => 'sms',
            'dashboard' => 'dashboard',
            default    => 'dashboard',
        };

        match ($channel) {
            'email'     => $this->sendEmail($recipient, $data['subject'] ?? 'Notification', $data['view'] ?? 'emails.default', $data),
            'sms'       => $this->sendSms($recipient, $data['message'] ?? ''),
            'dashboard' => $this->sendDashboard($recipient, $type, $data['subject'] ?? 'Notification', $data['message'] ?? ''),
        };
    }

    public function sendEmail($recipient, $subject, $view, $data): void
    {
        try {
            $to = $recipient->email ?? $recipient;
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (\Exception $e) {
            $recipientInfo = is_object($recipient) ? ($recipient->email ?? 'unknown') : $recipient;
            Log::error("Email sending failed to {$recipientInfo}: " . $e->getMessage());
        }
    }

    public function sendSms($phone, $message): void
    {
        $logEntry = "[SMS] To: {$phone} | Message: {$message} | Time: " . Carbon::now()->toDateTimeString();
        Log::channel('sms')->info($logEntry);
    }

    public function sendDashboard($userId, $type, $subject, $message): void
    {
        Notification::create([
            'applicant_id' => is_numeric($userId) ? $userId : null,
            'admin_id'     => null,
            'type'         => $type,
            'subject'      => $subject,
            'message'      => $message,
            'channel'      => 'dashboard',
            'sent_at'      => Carbon::now(),
        ]);
    }

    public function accountActivated(Applicant $applicant): void
    {
        $subject = 'Account Activated';
        $message = "Dear {$applicant->first_name}, your account has been activated successfully. You can now log in and apply.";

        $this->sendEmail($applicant, $subject, 'emails.account-activated', [
            'applicant' => $applicant,
            'subject'   => $subject,
            'message'   => $message,
        ]);

        $this->sendDashboard($applicant->id, 'account_activated', $subject, $message);
    }

    public function applicationSubmitted(Application $app): void
    {
        $applicant = $app->applicant;
        $subject = 'Application Submitted';
        $message = "Dear {$applicant->first_name}, your application (GAF ID: {$app->gaf_id}) has been received successfully.";

        $this->sendEmail($applicant, $subject, 'emails.application-submitted', [
            'applicant' => $applicant,
            'application' => $app,
            'subject'   => $subject,
            'message'   => $message,
        ]);

        $this->sendDashboard($applicant->id, 'application_submitted', $subject, $message);
    }

    public function eligibilityResult(Application $app): void
    {
        $applicant = $app->applicant;
        $result = $app->eligibilityResult;
        $status = $result->overall_status ?? 'unknown';

        $subject = $status === 'eligible' ? 'Eligibility Passed' : 'Eligibility Failed';
        $message = $status === 'eligible'
            ? "Dear {$applicant->first_name}, you have passed the eligibility check for application {$app->gaf_id}."
            : "Dear {$applicant->first_name}, unfortunately you did not pass the eligibility check for application {$app->gaf_id}. Reasons: " . implode('; ', $result->rejection_reasons ?? []);

        $this->sendEmail($applicant, $subject, 'emails.eligibility-result', [
            'applicant' => $applicant,
            'application' => $app,
            'result'    => $result,
            'subject'   => $subject,
            'message'   => $message,
        ]);

        $this->sendDashboard($applicant->id, 'eligibility_result', $subject, $message);
    }

    public function shortlisted(Application $app, $code): void
    {
        $applicant = $app->applicant;
        $subject = 'Shortlisted';
        $message = "Dear {$applicant->first_name}, congratulations! You have been shortlisted for {$app->cycle->name}. Your verification code: {$code}.";

        $this->sendEmail($applicant, $subject, 'emails.shortlisted', [
            'applicant'   => $applicant,
            'application' => $app,
            'code'        => $code,
            'subject'     => $subject,
            'message'     => $message,
        ]);

        $this->sendSms($applicant->contact_number, $message);

        $this->sendDashboard($applicant->id, 'shortlisted', $subject, $message);
    }

    public function appointmentScheduled(Application $app, Appointment $apt): void
    {
        $applicant = $app->applicant;
        $subject = 'Appointment Scheduled';
        $message = "Dear {$applicant->first_name}, your screening appointment is scheduled for {$apt->scheduled_date} at {$apt->scheduled_time}, venue: {$apt->venue}.";

        $this->sendEmail($applicant, $subject, 'emails.appointment-scheduled', [
            'applicant'   => $applicant,
            'application' => $app,
            'appointment' => $apt,
            'subject'     => $subject,
            'message'     => $message,
        ]);

        $this->sendSms($applicant->contact_number, $message);

        $this->sendDashboard($applicant->id, 'appointment_scheduled', $subject, $message);
    }

    public function screeningReminder(Application $app, Appointment $apt): void
    {
        $applicant = $app->applicant;
        $subject = 'Screening Reminder';
        $message = "Reminder: Your screening is tomorrow ({$apt->scheduled_date}) at {$apt->scheduled_time}, {$apt->venue}. Please arrive on time.";

        $this->sendEmail($applicant, $subject, 'emails.screening-reminder', [
            'applicant'   => $applicant,
            'application' => $app,
            'appointment' => $apt,
            'subject'     => $subject,
            'message'     => $message,
        ]);

        $this->sendSms($applicant->contact_number, $message);

        $this->sendDashboard($applicant->id, 'screening_reminder', $subject, $message);
    }

    public function finalDecision(Application $app): void
    {
        $applicant = $app->applicant;
        $decision = $app->finalDecision;
        $status = $decision->decision ?? 'unknown';

        $subject = $status === 'approved' ? 'Application Approved' : 'Application Rejected';
        $message = $status === 'approved'
            ? "Dear {$applicant->first_name}, we are pleased to inform you that your application ({$app->gaf_id}) has been approved."
            : "Dear {$applicant->first_name}, we regret to inform you that your application ({$app->gaf_id}) has been rejected.";

        $this->sendEmail($applicant, $subject, 'emails.final-decision', [
            'applicant'   => $applicant,
            'application' => $app,
            'decision'    => $decision,
            'subject'     => $subject,
            'message'     => $message,
        ]);

        $this->sendDashboard($applicant->id, "final_decision_{$status}", $subject, $message);
    }
}
