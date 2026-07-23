<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Ai\AiGateway;
use App\Services\Eligibility\EligibilityService;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentVerification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Document $document;
    public int $tries = 3;
    public int $backoff = 30;

    /** How many ADDITIONAL AI retries after the initial attempt (total = MAX_AI_RETRIES + 1) */
    private const MAX_AI_RETRIES = 2;

    /** Retry delays in minutes, indexed by attempt number (0 = first retry) */
    private const RETRY_DELAYS = [5, 15];

    private const REQUIRED_DOC_TYPES = [
        'birth_certificate', 'certificate', 'national_id', 'photograph',
    ];

    private const DOCUMENT_TEMPLATES = [
        'birth_certificate' => <<<TMPL
Ghana Birth Certificate expected features:
- Header: "REPUBLIC OF GHANA" or "CERTIFICATE OF BIRTH" or "BIRTH CERTIFICATE"
- Registration number format: B/C/VOL.XX/XXXX or similar
- Child's full name, date of birth, place of birth
- Parents' names and nationality
- District Registrar signature and stamp
- Issuance date
TMPL,
        'certificate' => <<<TMPL
Ghana Educational Certificate expected features:
For WASSCE: WAEC logo, candidate name, index number (10 digits), subjects with grades (A1-F9), gold/red color scheme, "WEST AFRICAN SENIOR SCHOOL CERTIFICATE EXAMINATION" header
For Degree: University crest, institution name, graduate name, degree awarded, conferment date, Vice Chancellor signature
TMPL,
        'national_id' => <<<TMPL
Ghana Card expected features:
- "GHANA CARD" header with national colors
- Card serial number
- ID number format: GHA-XXXXXXXXX-X
- Surname, other names, date of birth, gender, nationality
- Holder photograph
- Hologram/security features
- Date of issue and expiry
TMPL,
        'photograph' => <<<TMPL
Passport photograph expected features:
- Plain white or light background
- Full face front view
- No shadows on face or background
- Subject looking directly at camera
- No sunglasses or headwear (except religious)
- Natural skin tones, no filters
TMPL,
    ];

    private const ANALYSIS_PROMPT = <<<PROMPT
You are a Ghana Armed Forces document verification officer. Analyze the provided document image and return ONLY valid JSON (no markdown, no code blocks) with this exact structure:

{
  "overall": {
    "verdict": "verified" or "rejected",
    "confidence": 0.0 to 1.0,
    "reasons": ["reason1", "reason2"]
  },
  "extracted_fields": {
    "document_number": "value or null",
    "full_name": "value or null",
    "date_of_birth": "value or null",
    "gender": "Male, Female, or null",
    "issue_date": "value or null",
    "expiry_date": "value or null"
  },
  "cross_reference": {
    "name_match": true or false,
    "dob_match": true or false,
    "nationality_match": true or false,
    "gender_match": true or false
  },
  "template_validation": {
    "has_required_fields": true or false,
    "has_official_stamps": true or false,
    "has_valid_format": true or false
  },
  "fraud_indicators": ["list of suspected issues or empty array"]
}

Rules:
- Be decisive and err on the side of verification. Only use "needs_review" if the document is genuinely unclear/unreadable or missing critical fields.
- Set confidence >= 0.5 if the document appears valid and matches reference data reasonably well.
- If the document matches the reference data and looks authentic, mark verified.
- If the document is clearly forged, tampered, or doesn't match at all (confidence >= 0.7), mark rejected.
- When in doubt, lean toward "verified" at lower confidence — let human reviewers catch false positives.
- Extract any visible text fields from the document. Compare gender from the document with the reference data if available.
PROMPT;

    private const VERIFY_THRESHOLDS = [
        'birth_certificate' => 0.5,
        'certificate'       => 0.5,
        'national_id'       => 0.6,
        'photograph'        => 0.7,
    ];

    private const REJECT_THRESHOLDS = [
        'birth_certificate' => 0.7,
        'certificate'       => 0.7,
        'national_id'       => 0.75,
        'photograph'        => 0.8,
    ];

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle(AiGateway $ai, NotificationService $notification, EligibilityService $eligibility): void
    {
        try {
            $attempts = $this->document->ai_verification_attempts ?? 0;
            $this->document->increment('ai_verification_attempts');
            $currentAttempt = $attempts + 1;

            $filePath = $this->document->file_path;
            if (!$filePath || !Storage::disk('public')->exists($filePath)) {
                Log::warning("Document file not found for AI verification", ['doc_id' => $this->document->id]);
                return;
            }

            $applicant = $this->document->application?->applicant;
            $docType = $this->document->document_type;
            $absolutePath = Storage::disk('public')->path($filePath);
            $template = self::DOCUMENT_TEMPLATES[$docType] ?? 'No specific template available.';

            // Log PDF status — AiGateway::convertToImage() handles conversion downstream
            $fileMime = @mime_content_type($absolutePath);
            if ($fileMime === 'application/pdf') {
                Log::info("PDF document queued for AI verification — will be converted to image by AiGateway", [
                    'doc_id' => $this->document->id,
                    'type'   => $docType,
                    'attempt' => $currentAttempt,
                ]);
            }

            $referenceData = [];
            if ($applicant) {
                $referenceData = [
                    'first_name'    => $applicant->first_name,
                    'last_name'     => $applicant->last_name,
                    'date_of_birth' => $applicant->date_of_birth?->format('Y-m-d'),
                    'nationality'   => $applicant->nationality,
                    'gender'        => $applicant->gender,
                ];
            }

            $result = $ai->analyzeDocument($absolutePath, $docType, [
                'reference_data'     => $referenceData,
                'document_template'  => $template,
                'analysis_prompt'    => self::ANALYSIS_PROMPT,
            ]);

            if (!$result['success']) {
                Log::error('AI document verification failed', [
                    'doc_id' => $this->document->id,
                    'error'  => $result['error'] ?? 'Unknown error',
                ]);
                // Don't retry on provider errors — the queue's built-in retry ($tries/$backoff) handles infra failures
                return;
            }

            $analysis = $result['data'];
            $parsed = $this->parseAnalysis($analysis);

            $overall    = $parsed['overall'] ?? [];
            $verdict    = $overall['verdict'] ?? 'needs_review';
            $confidence = (float) ($overall['confidence'] ?? 0);
            $reasons    = $overall['reasons'] ?? [];

            $application = $this->document->application;
            $applicantId = $application?->applicant_id;
            $gafId       = $application?->gaf_id ?? 'N/A';

            // Persist AI analysis data regardless of verdict
            $this->document->update([
                'ai_confidence'          => $confidence,
                'extracted_data'         => $parsed['extracted_fields'] ?? [],
                'cross_reference_results' => [
                    'cross_reference'      => $parsed['cross_reference'] ?? [],
                    'template_validation'  => $parsed['template_validation'] ?? [],
                    'fraud_indicators'     => $parsed['fraud_indicators'] ?? [],
                ],
                'ai_verified_at' => now(),
            ]);

            $verifyThreshold  = self::VERIFY_THRESHOLDS[$docType] ?? 0.5;
            $rejectThreshold  = self::REJECT_THRESHOLDS[$docType] ?? 0.7;
            $confidencePct    = round($confidence * 100);

            // ───→ VERIFIED ←──────────────────────────────────────────────
            if ($verdict === 'verified' && $confidence >= $verifyThreshold) {
                $this->document->update([
                    'verification_status' => 'verified',
                    'ai_confidence'       => $confidence,
                    'ai_verified_at'      => now(),
                ]);

                Log::info("Document auto-verified by AI", [
                    'doc_id'    => $this->document->id,
                    'type'      => $docType,
                    'confidence' => $confidence,
                    'attempt'   => $currentAttempt,
                ]);

                if ($applicantId) {
                    $notification->sendDashboard(
                        $applicantId,
                        'document_verified',
                        'Document Auto-Verified',
                        "Your {$docType} was verified with {$confidencePct}% confidence."
                    );
                }

                // 🚀 Auto-advance if all 4 required docs are now AI-verified
                $this->checkAutoAdvanceApplication($application, $notification, $eligibility);

            // ───→ REJECTED ←─────────────────────────────────────────────
            } elseif ($verdict === 'rejected' && $confidence >= $rejectThreshold) {
                $this->document->update([
                    'verification_status' => 'rejected',
                    'ai_confidence'       => $confidence,
                    'ai_verified_at'      => now(),
                ]);

                Log::info("Document auto-rejected by AI", [
                    'doc_id'    => $this->document->id,
                    'type'      => $docType,
                    'confidence' => $confidence,
                    'reasons'   => $reasons,
                ]);

                if ($applicantId) {
                    $notification->sendDashboard(
                        $applicantId,
                        'document_rejected',
                        'Document Auto-Rejected',
                        'Reason: ' . implode(', ', $reasons)
                    );
                }

                $notification->notifyAdminsByRole(
                    ['admin', 'super_admin'],
                    'document_rejected',
                    $gafId,
                    $reasons
                );

            // ───→ NEEDS REVIEW / UNCERTAIN ←────────────────────────────
            } else {
                // Store current state
                $this->document->update([
                    'verification_status' => 'needs_review',
                    'ai_confidence'       => $confidence,
                    'ai_verified_at'      => now(),
                ]);

                Log::info("Document needs review after AI check", [
                    'doc_id'    => $this->document->id,
                    'type'      => $docType,
                    'verdict'   => $verdict,
                    'confidence' => $confidence,
                    'attempt'   => $currentAttempt,
                ]);

                // 🔁 Retry with escalating delay if we haven't exhausted retries
                if ($currentAttempt <= self::MAX_AI_RETRIES) {
                    $delayMinutes = self::RETRY_DELAYS[$currentAttempt - 1] ?? 30;
                    self::dispatch($this->document)->delay(now()->addMinutes($delayMinutes));

                    Log::info("Scheduled AI re-verification retry {$currentAttempt}/" . self::MAX_AI_RETRIES, [
                        'doc_id'  => $this->document->id,
                        'retry_in' => "{$delayMinutes}min",
                    ]);

                    if ($applicantId) {
                        $notification->sendDashboard(
                            $applicantId,
                            'document_rechecking',
                            'Document Being Rechecked',
                            "AI confidence was low ({$confidencePct}%). The system will re-verify your {$docType} automatically."
                        );
                    }
                } else {
                    // Max retries exhausted — leave as needs_review for admin
                    if ($applicantId) {
                        $notification->sendDashboard(
                            $applicantId,
                            'document_needs_review',
                            'Document Needs Manual Review',
                            'The system could not auto-verify this document. An admin will review it shortly.' . ($reasons ? ' Reasons: ' . implode(', ', $reasons) : '')
                        );
                    }

                    $notification->documentNeedsReview($this->document);
                }
            }

        } catch (\Exception $e) {
            Log::error('Exception in document verification job', [
                'doc_id' => $this->document->id,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if all 4 required documents are now AI-verified.
     * If so, advance to documents_verified and run eligibility.
     */
    private function checkAutoAdvanceApplication(
        ?\App\Models\Application $application,
        NotificationService $notification,
        EligibilityService $eligibility
    ): void {
        if (!$application) {
            return;
        }

        // Fresh query to see the current state of ALL required docs
        $verifiedCount = $application->documents()
            ->whereIn('document_type', self::REQUIRED_DOC_TYPES)
            ->where('verification_status', 'verified')
            ->count();

        $allVerified = $verifiedCount === count(self::REQUIRED_DOC_TYPES);

        if (!$allVerified) {
            Log::info("Not all required docs verified yet ({$verifiedCount}/" . count(self::REQUIRED_DOC_TYPES) . ')', [
                'application_id' => $application->id,
            ]);
            return;
        }

        // Only auto-advance if application is still awaiting document verification
        if (!in_array($application->status, ['submitted', 'documents_verified'], true)) {
            Log::info('Skipping auto-advance — application already past document stage', [
                'application_id' => $application->id,
                'current_status' => $application->status,
            ]);
            return;
        }

        $application->update(['status' => 'documents_verified']);

        Log::info('All required documents AI-verified — advancing application status', [
            'application_id' => $application->id,
            'gaf_id'         => $application->gaf_id,
        ]);

        // Notify applicant
        $applicantId = $application->applicant_id;
        if ($applicantId) {
            $notification->sendDashboard(
                $applicantId,
                'documents_verified',
                '✅ All Documents Verified',
                'All your required documents have been verified by the system. Your eligibility is now being evaluated.'
            );
        }

        // Trigger eligibility evaluation
        $eligibility->evaluateAfterDocVerification($application);
    }

    private function parseAnalysis(array $data): array
    {
        if (isset($data['content']) && is_string($data['content'])) {
            $json = $this->extractJson($data['content']);
            if ($json) return $json;
        }

        if (isset($data['message']['content']) && is_string($data['message']['content'])) {
            $json = $this->extractJson($data['message']['content']);
            if ($json) return $json;
        }

        if (isset($data['overall'])) {
            return $data;
        }

        if (isset($data['message'])) {
            $msg = $data['message'];
            if (is_string($msg)) {
                $json = $this->extractJson($msg);
                if ($json) return $json;
            }
        }

        $json = $this->extractJson(json_encode($data));
        if ($json) return $json;

        Log::warning('parseAnalysis: could not extract structured JSON from AI response', [
            'doc_id' => $this->document->id,
            'keys'   => array_keys($data),
        ]);

        return [];
    }

    private function extractJson(string $text): ?array
    {
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);

        if (preg_match('/\{.*\}/s', $text, $match)) {
            $decoded = json_decode($match[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }
}
