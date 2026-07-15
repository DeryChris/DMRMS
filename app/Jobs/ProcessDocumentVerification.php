<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Ai\AiGateway;
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
    "issue_date": "value or null",
    "expiry_date": "value or null"
  },
  "cross_reference": {
    "name_match": true or false,
    "dob_match": true or false,
    "nationality_match": true or false
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
- Extract any visible text fields from the document.
PROMPT;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle(AiGateway $ai, NotificationService $notification): void
    {
        try {
            $this->document->increment('ai_verification_attempts');

            $filePath = $this->document->file_path;
            if (!$filePath || !Storage::disk('public')->exists($filePath)) {
                Log::warning("Document file not found for AI verification", ['doc_id' => $this->document->id]);
                return;
            }

            $applicant = $this->document->application?->applicant;
            $docType = $this->document->document_type;
            $absolutePath = Storage::disk('public')->path($filePath);
            $template = self::DOCUMENT_TEMPLATES[$docType] ?? 'No specific template available.';

            $referenceData = [];
            if ($applicant) {
                $referenceData = [
                    'first_name' => $applicant->first_name,
                    'last_name' => $applicant->last_name,
                    'date_of_birth' => $applicant->date_of_birth?->format('Y-m-d'),
                    'nationality' => $applicant->nationality,
                    'gender' => $applicant->gender,
                ];
            }

            $result = $ai->analyzeDocument($absolutePath, $docType, [
                'reference_data' => $referenceData,
                'document_template' => $template,
                'analysis_prompt' => self::ANALYSIS_PROMPT,
            ]);

            if (!$result['success']) {
                Log::error('AI document verification failed', [
                    'doc_id' => $this->document->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
                return;
            }

            $analysis = $result['data'];
            $parsed = $this->parseAnalysis($analysis);

            $this->document->update([
                'ai_confidence' => $parsed['overall']['confidence'] ?? null,
                'extracted_data' => $parsed['extracted_fields'] ?? [],
                'cross_reference_results' => [
                    'cross_reference' => $parsed['cross_reference'] ?? [],
                    'template_validation' => $parsed['template_validation'] ?? [],
                    'fraud_indicators' => $parsed['fraud_indicators'] ?? [],
                ],
                'ai_verified_at' => now(),
            ]);

            $verdict = $parsed['overall']['verdict'] ?? 'needs_review';
            $confidence = $parsed['overall']['confidence'] ?? 0;

            if ($verdict === 'verified' && $confidence >= 0.5) {
                $this->document->update([
                    'verification_status' => 'verified',
                ]);
                Log::info("Document auto-verified by AI", [
                    'doc_id' => $this->document->id,
                    'confidence' => $confidence,
                ]);
            } elseif ($verdict === 'rejected' && $confidence >= 0.7) {
                $this->document->update([
                    'verification_status' => 'rejected',
                ]);
                Log::info("Document auto-rejected by AI", [
                    'doc_id' => $this->document->id,
                    'confidence' => $confidence,
                    'reasons' => $parsed['overall']['reasons'] ?? [],
                ]);
            } else {
                $this->document->update([
                    'verification_status' => 'needs_review',
                ]);
                Log::info("Document needs manual review after AI check", [
                    'doc_id' => $this->document->id,
                    'verdict' => $verdict,
                    'confidence' => $confidence,
                ]);
                $notification->documentNeedsReview($this->document);
            }

        } catch (\Exception $e) {
            Log::error('Exception in document verification job', [
                'doc_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function parseAnalysis(array $data): array
    {
        $content = $data['content'] ?? $data['message'] ?? json_encode($data);

        if (is_string($content)) {
            $json = $this->extractJson($content);
            if ($json) {
                return $json;
            }
        }

        return $data['parsed'] ?? $data;
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
