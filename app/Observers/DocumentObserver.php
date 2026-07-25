<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Log;

class DocumentObserver
{
    /**
     * Handle the Document "updated" event.
     *
     * Universal safety net: whenever a document's verification_status changes to
     * 'rejected', check if the reason is disqualifying (mismatch/fraud/tampering).
     * If so, automatically set the application to 'rejected' and notify.
     *
     * This catches ALL code paths:
     *   - AI auto-rejection (ProcessDocumentVerification job)
     *   - Admin rejection (AdminWebController)
     *   - API/console/direct DB updates
     *   - Already-processed docs that get re-touched
     */
    public function updated(Document $document): void
    {
        // Only act when status JUST changed to 'rejected'
        if (!$document->wasChanged('verification_status')) {
            return;
        }
        if ($document->verification_status !== 'rejected') {
            return;
        }

        $application = $document->application;
        if (!$application) {
            return;
        }

        // Skip if application is already in a terminal state
        if (in_array($application->status, ['rejected', 'disqualified', 'selected', 'recruited'], true)) {
            return;
        }

        // ── Determine if this rejection is DISQUALIFYING ────────────────
        $isDisqualifying = self::isDisqualifyingRejection($document);

        if (!$isDisqualifying) {
            return;
        }

        // ── DISQUALIFY ──────────────────────────────────────────────────
        $applicant = $application->applicant;
        $docTypeLabel = str_replace('_', ' ', ucfirst($document->document_type));
        $reasonText = $document->rejection_reason ?? 'No specific reason provided.';

        Log::warning('Document auto-disqualified via observer', [
            'document_id'    => $document->id,
            'application_id' => $application->id,
            'doc_type'       => $document->document_type,
            'reason'         => $reasonText,
        ]);

        $application->update(['status' => 'rejected']);

        // Notify applicant
        if ($applicant) {
            $notification = app(NotificationService::class);

            $subject = 'Application Disqualified — Document Verification Failed';
            $message = "Your {$docTypeLabel} document was rejected due to: {$reasonText}. "
                     . "Your application for the current recruitment cycle has been disqualified.";

            $notification->sendDashboard($applicant->id, 'document_disqualified', $subject, $message);

            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Dear {$applicant->first_name} {$applicant->last_name},\n\n"
                    . "{$message}\n\n"
                    . "GAF ID: {$application->gaf_id}\n"
                    . "If you believe this decision is an error, please contact recruitment@gaf.mil.gh\n\n"
                    . "Ghana Armed Forces – Defence Manpower Recruitment Management System",
                    function ($mail) use ($applicant, $subject) {
                        $mail->to($applicant->email, "{$applicant->first_name} {$applicant->last_name}")
                             ->subject($subject);
                    }
                );
            } catch (\Exception $e) {
                Log::error("Disqualification email failed from observer", [
                    'applicant' => $applicant->email,
                    'error'     => $e->getMessage(),
                ]);
            }

            $notification->notifyAdminsByRole(
                ['admin', 'super_admin'],
                'document_disqualified',
                "Applicant Disqualified — Observer — {$docTypeLabel}",
                "{$application->gaf_id}: {$applicant->name} disqualified — {$docTypeLabel} rejected (disqualifying). {$reasonText}"
            );
        }
    }

    /**
     * Determine if a rejection is disqualifying (no re-upload allowed).
     *
     * Checks in order of reliability:
     * 1. fraud_flags JSON — non-empty = evidence of forgery/tampering
     * 2. cross_reference_results JSON — any mismatch flag = identity fraud
     * 3. rejection_reason text — keyword matching (catches legacy + admin rejections)
     *
     * Made public static so the re-evaluation command can reuse the same logic.
     */
    public static function isDisqualifyingRejection(Document $document): bool
    {
        // ── Check 1: fraud_flags (most reliable — set by AI) ────────────
        $fraudFlags = $document->fraud_flags;
        if (is_array($fraudFlags) && count($fraudFlags) > 0) {
            Log::debug('Disqualifying: fraud_flags present', [
                'doc_id' => $document->id,
                'flags'  => $fraudFlags,
            ]);
            return true;
        }

        // ── Check 2: cross_reference_results (set by AI) ────────────────
        $crossRef = $document->cross_reference_results;
        if (is_array($crossRef)) {
            $mismatchFields = ['name_match', 'dob_match', 'nationality_match', 'gender_match'];
            foreach ($mismatchFields as $field) {
                if (isset($crossRef[$field]) && $crossRef[$field] !== true && $crossRef[$field] !== 1) {
                    Log::debug('Disqualifying: cross-reference mismatch', [
                        'doc_id' => $document->id,
                        'field'  => $field,
                        'value'  => $crossRef[$field],
                    ]);
                    return true;
                }
            }
        }

        // ── Check 3: rejection_reason text keywords (legacy/admin) ──────
        $reason = $document->rejection_reason ?? '';
        if (empty($reason)) {
            return false;
        }

        $disqualifyingKeywords = [
            'not match', 'mismatch', 'do not match',
            'fraud', 'forgery', 'forged', 'tamper', 'tampered', 'tampering',
            'digital manipulation', 'not original',
            'information does not match',
            'suspected to be a forgery',
            'signs of tampering',
            'appears not to be original',
        ];

        $reasonLower = mb_strtolower($reason);
        foreach ($disqualifyingKeywords as $keyword) {
            if (str_contains($reasonLower, $keyword)) {
                Log::debug('Disqualifying: keyword match in rejection_reason', [
                    'doc_id'  => $document->id,
                    'keyword' => $keyword,
                    'reason'  => $reason,
                ]);
                return true;
            }
        }

        return false;
    }
}
