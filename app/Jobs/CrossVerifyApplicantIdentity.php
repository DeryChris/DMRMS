<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Ai\AiGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CrossVerifyApplicantIdentity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public const CROSS_VERIFY_PROMPT = <<<PROMPT
You are a FORENSIC IDENTITY EXAMINER for the Ghana Armed Forces recruitment system. Your task is to ANALYZE and CROSS-VERIFY all uploaded documents to determine whether they belong to the SAME INDIVIDUAL.

Do NOT verify each document in isolation. Instead, COMPARE the information across every uploaded document and identify whether the details are consistent.

Compare and validate ALL relevant fields across documents, including but not limited to:
- Full name (including spelling, order, and initials)
- Date of birth
- Nationality
- Gender
- Identification numbers (where applicable)
- Student or employee numbers
- Institution or organization
- Certificate holder name
- Parent or guardian names (if applicable)
- Place of birth
- Addresses
- Signatures (where possible)
- Photographs (where present — compare facial features, head shape, facial structure)
- Issue dates and document timelines
- Any other identifying information

Account for reasonable variations:
- Middle names or initials being present in one document but omitted in another
- Minor formatting differences (uppercase vs. lowercase, punctuation, spacing)
- Common abbreviations or naming conventions

Flag:
- Exact matches
- Probable matches (with confidence level)
- Potential inconsistencies
- Definite mismatches
- Missing information that prevents verification

Return ONLY valid JSON (no markdown, no code blocks) with this exact structure:

{
  "overall_status": "match" | "possible_match" | "mismatch" | "insufficient",
  "overall_confidence": 0.0 to 1.0,
  "comparison_summary": {
    "full_name": {
      "status": "match" | "possible_match" | "mismatch" | "missing",
      "values": { "doc_label": "extracted value", ... },
      "explanation": "why this field matched or failed"
    },
    "date_of_birth": {
      "status": "match" | "possible_match" | "mismatch" | "missing",
      "values": { "doc_label": "extracted value", ... },
      "explanation": "why this field matched or failed"
    },
    "nationality": {
      "status": "match" | "possible_match" | "mismatch" | "missing",
      "values": { "doc_label": "extracted value", ... },
      "explanation": ""
    },
    "gender": {
      "status": "match" | "possible_match" | "mismatch" | "missing",
      "values": { "doc_label": "extracted value", ... },
      "explanation": ""
    },
    "id_number": {
      "status": "match" | "possible_match" | "mismatch" | "missing",
      "values": { "doc_label": "extracted value", ... },
      "explanation": ""
    },
    "photograph": {
      "status": "match" | "possible_match" | "mismatch" | "missing",
      "values": { "doc_label": "description of photo", ... },
      "explanation": "facial feature comparison across photos"
    },
    "additional_fields": [
      {
        "field_name": "place_of_birth",
        "status": "match" | "mismatch" | "missing",
        "values": { "doc_label": "value", ... },
        "explanation": ""
      }
    ]
  },
  "detected_inconsistencies": [
    "list each inconsistency found — empty array if none"
  ],
  "supporting_evidence": "detailed explanation of why each field matched or failed across documents",
  "final_verdict": "clear statement of whether the documents belong to the same individual, justified by all available evidence"
}

CRITICAL RULES:

1. COMPARE ACROSS DOCUMENTS: Every field must be compared between ALL documents. Extract the value from each document and check consistency.

2. PHOTOGRAPH COMPARISON: If a photograph is present in multiple documents (e.g., national_id and a separate photograph upload), compare facial features, head shape, face structure, and general appearance. Note any discrepancies.

3. CONFIDENCE SCORING:
   - confidence >= 0.90 → All fields match perfectly across all documents, photographs clearly match
   - confidence >= 0.70 → Most fields match, minor reasonable variations (initials, formatting)
   - confidence >= 0.40 → Some fields match, others have inconsistencies or missing data
   - confidence < 0.40 → Significant mismatches found — the documents likely belong to different individuals

4. STATUS GUIDANCE:
   - "match" → all key identity fields (name, DOB, nationality, gender, photo) are consistent
   - "possible_match" → most fields match but some have reasonable variations or some data is missing
   - "mismatch" → one or more key fields definitively do not match
   - "insufficient" → not enough data to make a determination

5. INCONSISTENCIES: Report EVERY inconsistency, no matter how minor. Empty array only if all fields match perfectly across all documents.

6. FINAL VERDICT: Must be a comprehensive conclusion that considers ALL evidence, not just a single field. State whether the documents belong to the same person and why.

Remember: This is military recruitment. Identity fraud is a serious national security concern. Be THOROUGH, be PRECISE, and never make assumptions without evidence.
PROMPT;

    public function __construct(
        public int $applicationId
    ) {}

    public function handle(AiGateway $ai): void
    {
        try {
            $application = \App\Models\Application::with('documents', 'applicant')->find($this->applicationId);

            if (!$application) {
                Log::warning('CrossVerifyApplicantIdentity: Application not found', ['id' => $this->applicationId]);
                return;
            }

            $docs = $application->documents;
            if ($docs->isEmpty()) {
                Log::warning('CrossVerifyApplicantIdentity: No documents found', ['application_id' => $this->applicationId]);
                return;
            }

            $applicant = $application->applicant;
            if (!$applicant) {
                Log::warning('CrossVerifyApplicantIdentity: No applicant found', ['application_id' => $this->applicationId]);
                return;
            }

            // Build document list with file paths
            $documents = [];
            foreach ($docs as $doc) {
                if (!$doc->file_path || !Storage::disk('public')->exists($doc->file_path)) {
                    Log::warning('CrossVerifyApplicantIdentity: Document file missing', [
                        'doc_id' => $doc->id,
                        'path'   => $doc->file_path,
                    ]);
                    continue;
                }

                $documents[] = [
                    'path'  => Storage::disk('public')->path($doc->file_path),
                    'type'  => $doc->document_type,
                    'label' => str_replace('_', ' ', ucfirst($doc->document_type)),
                ];
            }

            if (empty($documents)) {
                Log::warning('CrossVerifyApplicantIdentity: No valid document files', ['application_id' => $this->applicationId]);
                return;
            }

            if (count($documents) < 2) {
                Log::info('CrossVerifyApplicantIdentity: Fewer than 2 documents — skipping cross-verification', [
                    'application_id' => $this->applicationId,
                    'doc_count'      => count($documents),
                ]);
                return;
            }

            // Build reference data from applicant
            $referenceData = [
                'first_name'    => $applicant->first_name,
                'last_name'     => $applicant->last_name,
                'date_of_birth' => $applicant->date_of_birth?->format('Y-m-d'),
                'nationality'   => $applicant->nationality,
                'gender'        => $applicant->gender,
            ];

            Log::info('CrossVerifyApplicantIdentity: Starting cross-verification', [
                'application_id' => $this->applicationId,
                'doc_count'      => count($documents),
                'doc_types'      => array_column($documents, 'type'),
            ]);

            $result = $ai->crossVerifyDocuments($documents, $referenceData, self::CROSS_VERIFY_PROMPT);

            if (!$result['success']) {
                Log::error('CrossVerifyApplicantIdentity: AI cross-verification failed', [
                    'application_id' => $this->applicationId,
                    'error'          => $result['error'] ?? 'Unknown error',
                ]);
                return;
            }

            $verificationData = $result['data'];

            // Store result on the application
            $application->update([
                'identity_verification' => $verificationData,
            ]);

            Log::info('CrossVerifyApplicantIdentity: Completed successfully', [
                'application_id' => $this->applicationId,
                'overall_status' => $verificationData['overall_status'] ?? 'unknown',
                'confidence'     => $verificationData['overall_confidence'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in CrossVerifyApplicantIdentity', [
                'application_id' => $this->applicationId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
