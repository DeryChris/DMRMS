<?php

namespace App\Services\Notification;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use App\Mail\EmailVerificationMail;
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

    public function notifyAdminsByRole(array|string $roles, string $type, string $subject, string $message): void
    {
        $roles = (array) $roles;

        $admins = User::where(function ($q) use ($roles) {
            $q->whereHas('roles', fn($r) => $r->whereIn('name', $roles))
              ->orWhereIn('role', $roles);
        })->get();

        foreach ($admins as $admin) {
            Notification::create([
                'admin_id' => $admin->id,
                'type' => $type,
                'subject' => $subject,
                'message' => $message,
                'channel' => 'dashboard',
                'sent_at' => Carbon::now(),
            ]);
        }
    }

    public function registrationWelcome(Applicant $applicant): void
    {
        $subject = 'Welcome to Ghana Armed Forces Recruitment';
        $message = "Dear {$applicant->first_name}, thank you for registering with the Ghana Armed Forces Recruitment Portal. Your account is now active. You can log in and start your application.";
        $this->sendEmail($applicant, $subject, 'emails.account-activated', ['applicant' => $applicant, 'subject' => $subject, 'message' => $message]);
        $this->sendDashboard($applicant->id, 'registration_welcome', $subject, $message);
    }

    public function accountActivated(Applicant $applicant): void
    {
        $subject = 'Account Activated';
        $message = "Dear {$applicant->first_name}, your account has been activated successfully. You can now log in and apply.";
        $this->sendEmail($applicant, $subject, 'emails.account-activated', ['applicant' => $applicant, 'subject' => $subject, 'message' => $message]);
        $this->sendDashboard($applicant->id, 'account_activated', $subject, $message);
    }

    public function applicationSubmitted(Application $app): void
    {
        $applicant = $app->applicant;
        $subject = 'Application Submitted';
        $message = "Dear {$applicant->first_name}, your application (GAF ID: {$app->gaf_id}) has been received successfully.";
        $this->sendEmail($applicant, $subject, 'emails.application-submitted', ['applicant' => $applicant, 'application' => $app, 'subject' => $subject, 'message' => $message]);
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
        $this->sendEmail($applicant, $subject, 'emails.eligibility-result', ['applicant' => $applicant, 'application' => $app, 'result' => $result, 'subject' => $subject, 'message' => $message]);
        $this->sendDashboard($applicant->id, 'eligibility_result', $subject, $message);
    }

    public function shortlisted(Application $app): void
    {
        $applicant = $app->applicant;
        $subject = 'Shortlisted';
        $message = "Dear {$applicant->first_name}, congratulations! You have been shortlisted for {$app->cycle->name}. You will receive your screening appointment and verification code shortly.";
        $this->sendEmail($applicant, $subject, 'emails.shortlisted', ['applicant' => $applicant, 'application' => $app, 'subject' => $subject, 'message' => $message]);
        $this->sendSms($applicant->contact_number, $message);
        $this->sendDashboard($applicant->id, 'shortlisted', $subject, $message);
    }

    public function appointmentScheduled(Application $app, Appointment $apt, ?\App\Models\VerificationCode $verificationCode = null): void
    {
        $applicant = $app->applicant;
        $code = $verificationCode?->code_value;
        $qrPath = $verificationCode?->qr_code_path;
        $codeText = $code ? " Your verification code: {$code}." : '';
        $subject = 'Appointment Scheduled';
        $message = "Dear {$applicant->first_name}, your screening appointment is scheduled for {$apt->scheduled_date} at {$apt->scheduled_time}, venue: {$apt->venue}.{$codeText}";
        $this->sendEmail($applicant, $subject, 'emails.appointment-scheduled', [
            'applicant' => $applicant,
            'application' => $app,
            'appointment' => $apt,
            'verificationCode' => $verificationCode,
            'code' => $code,
            'qrPath' => $qrPath,
            'subject' => $subject,
            'message' => $message,
        ]);
        $this->sendSms($applicant->contact_number, $message);
        $this->sendDashboard($applicant->id, 'appointment_scheduled', $subject, $message);
    }

    public function screeningReminder(Application $app, Appointment $apt): void
    {
        $applicant = $app->applicant;
        $subject = 'Screening Reminder';
        $message = "Reminder: Your screening is tomorrow ({$apt->scheduled_date}) at {$apt->scheduled_time}, {$apt->venue}. Please arrive on time.";
        $this->sendEmail($applicant, $subject, 'emails.screening-reminder', ['applicant' => $applicant, 'application' => $app, 'appointment' => $apt, 'subject' => $subject, 'message' => $message]);
        $this->sendSms($applicant->contact_number, $message);
        $this->sendDashboard($applicant->id, 'screening_reminder', $subject, $message);
    }

    public function documentsVerified(Application $app): void
    {
        $applicant = $app->applicant;
        $subject = 'Documents Verified';
        $message = "Dear {$applicant->first_name}, all your required documents have been verified for application {$app->gaf_id}. Eligibility check is in progress.";
        $this->sendEmail($applicant, $subject, 'emails.documents-verified', ['applicant' => $applicant, 'application' => $app, 'subject' => $subject, 'message' => $message]);
        $this->sendDashboard($applicant->id, 'documents_verified', $subject, $message);
    }

    public function finalDecisionPending(Application $app): void
    {
        $applicant = $app->applicant;
        $subject = 'Final Decision Pending';
        $message = "Dear {$applicant->first_name}, your screening process is complete for application {$app->gaf_id}. Your application is now pending final committee review.";
        $this->sendEmail($applicant, $subject, 'emails.final-decision-pending', ['applicant' => $applicant, 'application' => $app, 'subject' => $subject, 'message' => $message]);
        $this->sendDashboard($applicant->id, 'final_decision_pending', $subject, $message);
    }

    public function finalDecision(Application $app): void
    {
        $applicant = $app->applicant;
        $decision = $app->finalDecision;
        if (!$decision) {
            return;
        }

        $status = $decision->decision;

        $battalion = $app->training_battalion ?? config('recruitment.auto_recruit.default_training_battalion', 'GAF Training Depot');

        $messages = [
            'selected' => [
                'subject' => 'Congratulations — You\'ve Been Selected',
                'message' => "Dear {$applicant->first_name}, we are pleased to inform you that your application ({$app->gaf_id}) has been approved. Welcome to the Ghana Armed Forces. Further instructions will be sent to you.",
            ],
            'admitted' => [
                'subject' => 'Congratulations — You\'ve Been Selected',
                'message' => "Dear {$applicant->first_name}, we are pleased to inform you that your application ({$app->gaf_id}) has been approved. Welcome to the Ghana Armed Forces. Further instructions will be sent to you.",
            ],
            'recruited' => [
                'subject' => 'You\'ve Been Recruited — Report for Training',
                'message' => "Dear {$applicant->first_name}, congratulations! You have been officially recruited into the Ghana Armed Forces. Report to {$battalion} for training. Your enrollment has been processed.",
            ],
            'rejected' => [
                'subject' => 'Application Status Update',
                'message' => "Dear {$applicant->first_name}, we regret to inform you that your application ({$app->gaf_id}) has been rejected. We wish you the best in your future endeavors.",
            ],
            'deferred' => [
                'subject' => 'Application Under Further Review',
                'message' => "Dear {$applicant->first_name}, your application ({$app->gaf_id}) has been deferred for further review. You will be notified of the final decision.",
            ],
            'reserve' => [
                'subject' => 'You\'ve Been Placed on the Reserve List',
                'message' => "Dear {$applicant->first_name}, your application ({$app->gaf_id}) has been placed on the reserve list. You may be promoted if vacancies become available.",
            ],
        ];

        $info = $messages[$status] ?? [
            'subject' => 'Application Decision',
            'message' => "Dear {$applicant->first_name}, a decision has been made on your application ({$app->gaf_id}).",
        ];

        $this->sendEmail($applicant, $info['subject'], 'emails.final-decision', [
            'applicant' => $applicant,
            'application' => $app,
            'decision' => $decision,
            'subject' => $info['subject'],
            'message' => $info['message'],
        ]);
        $this->sendDashboard($applicant->id, "final_decision_{$status}", $info['subject'], $info['message']);
    }

    public function cohortExpanded(Application $app, string $cycleName, int $newTotal): void
    {
        $applicant = $app->applicant;
        $subject = 'Cohort Expanded — Additional Selections Made';
        $message = "Dear {$applicant->first_name}, additional candidates have been selected from the reserve list to fill remaining vacancies in the {$cycleName} cycle. Your cohort has been expanded to {$newTotal} candidates.";

        $this->sendEmail($applicant, $subject, 'emails.cohort-expanded', [
            'applicant' => $applicant,
            'application' => $app,
            'cycleName' => $cycleName,
            'newTotal' => $newTotal,
            'subject' => $subject,
            'message' => $message,
        ]);
        $this->sendDashboard($applicant->id, 'cohort_expanded', $subject, $message);
    }

    public function sendBack(Application $app, string $fromStatus, string $reason): void
    {
        $applicant = $app->applicant;
        $subject = 'Application Returned for Re-review';
        $toLabel = str_replace('_', ' ', $app->status);
        $fromLabel = str_replace('_', ' ', $fromStatus);
        $message = "Dear {$applicant->first_name}, your application ({$app->gaf_id}) has been returned from '{$fromLabel}' to '{$toLabel}' for re-review. Reason: {$reason}";

        $this->sendEmail($applicant, $subject, 'emails.send-back', [
            'applicant' => $applicant,
            'application' => $app,
            'fromStatus' => $fromLabel,
            'toStatus' => $toLabel,
            'reason' => $reason,
            'subject' => $subject,
            'message' => $message,
        ]);
        $this->sendDashboard($applicant->id, 'send_back', $subject, $message);

        $this->notifyAdminsByRole(
            ['admin', 'super_admin', 'recruitment_officer'],
            'send_back',
            'Applicant Returned for Re-review',
            "{$applicant->name} ({$app->gaf_id}) returned from {$fromLabel} to {$toLabel} by " . (auth()->user()->name ?? 'System') . ". Reason: {$reason}"
        );
    }

    public function documentNeedsReview(\App\Models\Document $document): void
    {
        $app = $document->application;
        $applicantName = $app?->applicant?->name ?? 'Unknown';
        $gafId = $app?->gaf_id ?? 'N/A';
        $typeLabel = str_replace('_', ' ', ucfirst($document->document_type));
        $subject = 'Document Needs Manual Review';
        $message = "{$typeLabel} for {$applicantName} ({$gafId}) requires manual review. AI confidence: " . ($document->ai_confidence ?? 'N/A');

        $this->notifyAdminsByRole(
            ['admin', 'super_admin', 'recruitment_officer'],
            'document_needs_review',
            $subject,
            $message
        );
    }

    public function sendEmailVerificationCode(Applicant $applicant, string $code): void
    {
        Mail::to($applicant)->send(new EmailVerificationMail($applicant, $code));
    }

    public function sendSmsVerificationCode(Applicant $applicant, string $code): void
    {
        $message = "Your verification code is: {$code}. Valid for 30 minutes.";
        $this->sendSms($applicant->contact_number, $message);
    }
}
