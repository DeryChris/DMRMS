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
=== GHANA BIRTH CERTIFICATE — FORENSIC REFERENCE TEMPLATE ===

AUTHENTIC DOCUMENT EXPECTED FEATURES:

1. HEADER & BRANDING:
   - "REPUBLIC OF GHANA" coat of arms at top center (black and white or gold)
   - "CERTIFICATE OF BIRTH" or "BIRTH CERTIFICATE" in bold, official font
   - Births and Deaths Registry header
   - Official document title in English

2. SECURITY FEATURES:
   - Watermark: "GOVERNMENT OF GHANA" or crest watermark visible when held to light
   - Serial/Registration number: Format "B/C/VOL.XX/XXXX" or "BC-YYYY-XXXXX" — must be printed, not handwritten
   - Embossed or stamped official seal of the District Registrar
   - Security background pattern (guilloche-style fine lines)
   - Microprinting may be present on border edges

3. REQUIRED DATA FIELDS (all must be present and printed):
   - Child's full name (Surname, Other names)
   - Date of birth (DD/MM/YYYY format)
   - Sex (Male/Female)
   - Place of birth (town/city, region)
   - District of registration
   - Father's full name and nationality
   - Mother's full name and nationality
   - Date of registration/issuance
   - Registration number

4. OFFICIAL ELEMENTS:
   - Signature of District Registrar of Births and Deaths (original ink, NOT photocopied)
   - Official stamp/seal of the Births and Deaths Registry
   - Stamp must overlap the registrar's signature (anti-forgery measure)
   - Date of issue must precede child's age logically

5. COMMON FORGERY RED FLAGS:
   - Wrong font typeface (typewriter fonts vs modern digital printing)
   - Missing or fake-looking coat of arms (wrong proportions, blurry)
   - Photocopied signature (uniform pixel color, no ink variation)
   - Missing serial number or obviously fake number format
   - Handwritten entries on an officially printed form
   - Spelling errors in official text
   - Irregular border patterns or cut marks
   - White-out/correction fluid on any field
   - Dates that don't align (e.g., registration date before birth)
TMPL,
        'certificate' => <<<TMPL
=== GHANA EDUCATIONAL CERTIFICATE — FORENSIC REFERENCE TEMPLATE ===

AUTHENTIC DOCUMENT EXPECTED FEATURES (by type):

--- WASSCE CERTIFICATE ---
1. HEADER & BRANDING:
   - WAEC (West African Examinations Council) logo — official crest with torch
   - "WEST AFRICAN SENIOR SCHOOL CERTIFICATE EXAMINATION" in bold header
   - Gold/yellow and red color scheme (authentic WAEC colors)
   - "WASSCE" and year prominently displayed
   - Security background: intricate fine-line patterns in gold/green

2. SECURITY FEATURES:
   - WAEC embossed gold foil stamp/seal on bottom right
   - Watermark with WAEC logo pattern
   - Microprinted text along borders
   - Candidate photograph with security overlay
   - Holographic security strip (on newer certificates)
   - Unique candidate index number: 10-digit format (e.g., 4120101001)
   - Serial number printed on certificate

3. REQUIRED DATA FIELDS:
   - Candidate's full name (Surname, First name, Middle name)
   - Index/Candidate number (10 digits)
   - Name and address of school attended
   - Subjects taken (minimum 6-8 subjects)
   - Grades for each subject: A1, B2, B3, C4, C5, C6, D7, E8, F9
   - Overall grade summary
   - Examination year and month
   - Date of issue

4. OFFICIAL ELEMENTS:
   - Signature of WAEC Registrar
   - WAEC official stamp (must overlap photograph corner)
   - Embossed WAEC seal
   - Photograph must be authenticated with WAEC stamp

--- DEGREE / DIPLOMA CERTIFICATE ---
1. HEADER & BRANDING:
   - University crest/coat of arms at top center
   - Official university name (full legal name, not abbreviation)
   - "CERTIFICATE" or "DEGREE" or "DIPLOMA" in formal script
   - Motto of the university (usually below crest)

2. SECURITY FEATURES:
   - University embossed gold seal
   - Security watermarked paper
   - Serial number/folio number
   - Registrar's signature and official stamp
   - Vice Chancellor or Principal signature
   - Holographic security element (modern universities)

3. REQUIRED DATA FIELDS:
   - Full name of graduate
   - Degree/Diploma title (e.g., Bachelor of Science in Computer Science)
   - Class of degree (First Class, Second Class Upper/Lower, Pass)
   - Date of award/conferment
   - Programme of study and major
   - Student ID/Matriculation number

4. OFFICIAL ELEMENTS:
   - Vice Chancellor signature
   - Registrar signature
   - University official seal/stamp
   - Date of issuance
   - Signatures must be original, not printed

--- COMMON FORGERY RED FLAGS FOR ALL CERTIFICATES ---
   - Wrong crest/logo proportions or colors
   - Missing or fake-looking WAEC seal
   - Index numbers with wrong digit count
   - Grades that don't exist in the WASSCE system (e.g., "A*")
   - Font mismatch between pre-printed text and variable data
   - Missing university motto or incorrect motto
   - Photocopied/printed signatures (no ink variation)
   - No embossed seal or flat/fake-looking seal
   - Inconsistent date formats within same document
   - Spelling errors in graduate name vs application
   - Background security pattern doesn't extend to edges
   - Obvious Photoshop artifacts around seal/photograph
   - Signature of VC/Registrar from wrong era/office holder
TMPL,
        'national_id' => <<<TMPL
=== GHANA CARD (NATIONAL ID) — FORENSIC REFERENCE TEMPLATE ===

AUTHENTIC DOCUMENT EXPECTED FEATURES:

1. HEADER & BRANDING:
   - "GHANA CARD" header strip with Ghana national colors (red, gold, green, black star)
   - NIA (National Identification Authority) logo
   - "REPUBLIC OF GHANA" text
   - Card design: predominantly green and gold/blue-green tones

2. SECURITY FEATURES:
   - Holographic overlay with Ghana maps/coat of arms (tilt to see)
   - Microprinted text visible under magnification
   - Ghost/matte portrait (secondary smaller photo, usually right side)
   - Card serial number (printed, embossed or laser-engraved)
   - ID number format: GHA-XXXXXXXXX-XX (exactly 9 digits + 2 check digits)
   - Machine Readable Zone (MRZ) at bottom — 2-3 lines of OCR-B font text
   - UV-reactive features (glow under black light)
   - Tactile/embossed elements (raised text on surname, card number)
   - Fine-line guilloche patterns on background
   - Color-shifting ink on certain elements

3. REQUIRED DATA FIELDS (all printed, not handwritten):
   - Surname (bold or distinct format)
   - Other/Given names
   - Date of birth (DD/MM/YYYY)
   - Gender (M/F)
   - Nationality (GHANAIAN)
   - Holder photograph (high quality, frontal face)
   - Date of issue
   - Date of expiry (usually 10 years from issue)
   - Place of issue

4. OFFICIAL ELEMENTS:
   - NIA Director/Registrar signature (digitally printed)
   - Official hologram covering part of photo
   - Card must be polycarbonate/PVC material (not paper)
   - Laser-engraved personalization (not inkjet printed)

5. CRITICAL FORGERY RED FLAGS:
   - Missing hologram or flat shiny overlay without depth
   - Wrong ID number format (incorrect digit count, wrong prefix)
   - Inkjet or thermal transfer printing (should be laser engraved)
   - No ghost portrait or ghost portrait is low quality
   - MRZ doesn't validate (check digit algorithm)
   - Photo appears cut-and-pasted (different lighting, pixel boundaries)
   - Wrong colors (too dark, too light, wrong shade of green)
   - Missing or fake microprinting
   - No tactile/raised elements (card surface is completely flat)
   - Signature is missing or obviously printed
   - Font mismatch between pre-printed and personalized text
   - Hologram doesn't change when tilted
   - Card edges show lamination separation
   - Expired card (check issue/expiry dates against current date)
TMPL,
        'photograph' => <<<TMPL
=== PASSPORT PHOTOGRAPH — FORENSIC REFERENCE TEMPLATE ===

AUTHENTIC DOCUMENT EXPECTED FEATURES:

1. BACKGROUND & LIGHTING:
   - Plain white or off-white background (R:255 G:255 B:255 or near)
   - NO shadows on background whatsoever
   - Even lighting across entire face (no hotspots or dark patches)
   - No colored, patterned, or gradient backgrounds
   - Background must be solid with no visible texture or lines

2. SUBJECT POSE & FRAMING:
   - Full face front view (both ears equally visible)
   - Subject looking directly at camera (not angled/turned)
   - Neutral expression with mouth closed (no smiling showing teeth)
   - Eyes open, clearly visible (no hair covering eyes)
   - Head centered in frame, full head from crown to chin
   - No tilt of the head

3. HEADWEAR & ACCESSORIES:
   - NO headwear EXCEPT for religious purposes (hijab, turban)
   - Religious headwear must not obscure facial features
   - NO sunglasses or tinted glasses
   - Regular prescription glasses OK but:
       - No glare/reflection on lenses
       - Eyes must be clearly visible through glasses
       - No thick frames obscuring eyes
   - NO face veil, mask, or covering

4. IMAGE QUALITY & AUTHENTICITY:
   - Sharp focus, no blur (motion or defocus)
   - Natural skin tones — NO filters, beauty mode, or AI enhancement
   - No red-eye or unnatural color casts
   - Resolution must be sufficient (450x540 pixels minimum)
   - Aspect ratio approximately 5:6
   - No watermarks, timestamps, or digital overlays
   - File must be original camera output, not screenshot of photo
   - No compression artifacts (JPEG blockiness)

5. FORGERY & MANIPULATION RED FLAGS:
   - Background replacement (hair edge artifacts, color bleeding at edges)
   - AI-generated or AI-enhanced faces (check for too-perfect skin, inconsistent eye reflections)
   - Head replacement (neck/skin tone mismatch, different lighting on head vs body)
   - Filter/skin smoothing (unnatural pore absence, plastic appearance)
   - Background removal with jagged or unnatural edges
   - Shadows inconsistent with assumed lighting direction
   - Same photo used from a different application (EXIF data mismatch)
   - Face not centered (wrong crop)
   - Size/dimensions altered (stretched, squashed aspect ratio)
   - Date metadata shows photo is very old
   - Mobile screenshot instead of original photo (check for UI elements in corners)
   - EXIF data stripped or manipulated
TMPL,
    ];

    private const ANALYSIS_PROMPT = <<<PROMPT
You are a FORENSIC DOCUMENT EXAMINER for the Ghana Armed Forces recruitment system. Your role is to scrutinise every document with the highest level of rigor — military recruitment security depends on your accuracy. Be THOROUGH, be DETAILED, and NEVER assume authenticity. Every document is considered SUSPECT until proven genuine.

Analyze the provided document image at the pixel level and return ONLY valid JSON (no markdown, no code blocks) with this exact structure:

{
  "overall": {
    "verdict": "verified" | "rejected" | "needs_review",
    "confidence": 0.0 to 1.0,
    "reasons": ["detailed reason 1", "detailed reason 2"]
  },
  "extracted_fields": {
    "document_number": "value or null",
    "full_name": "value or null",
    "date_of_birth": "value or null (ISO format YYYY-MM-DD if possible)",
    "gender": "Male, Female, or null",
    "issue_date": "value or null (ISO format YYYY-MM-DD if possible)",
    "expiry_date": "value or null (ISO format YYYY-MM-DD if possible)",
    "issuing_authority": "value or null",
    "nationality": "value or null"
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
    "has_valid_format": true or false,
    "has_security_features": true or false,
    "font_consistent": true or false,
    "logo_crest_present": true or false
  },
  "fraud_indicators": [
    "list of ALL suspected issues, no matter how minor — empty array only if document is 100% clean"
  ],
  "forensic_analysis": {
    "image_integrity": "describe any signs of digital manipulation, inconsistent lighting, pixel anomalies, cut-and-paste artifacts, or compression inconsistencies",
    "font_analysis": "describe font consistency — watch for mismatched typefaces, irregular spacing, characters that don't match the rest",
    "security_feature_check": "describe visible security features: watermarks, holograms, microprinting, guilloche patterns, UV-reactive elements, embossed seals, serial number font/alignment",
    "signature_analysis": "evaluate any signatures present — look for photocopied signatures, stamped vs original ink, trembling lines indicating tracing",
    "edge_detection": "check for irregular borders, cut marks, photoshopping around edges, mismatched background textures near boundaries",
    "date_validity": "verify the document's issue/expiry dates are logical (not future-dated, not expired for unreasonable periods, date formatting matches official standards)"
  }
}

CRITICAL SCRUTINY RULES — You MUST follow these without exception:

1. **PRESUME GUILTY**: Treat every document as potentially fraudulent until proven otherwise. This is military recruitment — there is zero tolerance for forged documents.

2. **VERIFY EVERY FIELD**: Cross-reference EVERY extracted field against the reference data provided. A mismatch on name, DOB, nationality, or gender is grounds for REJECTION (not verification).

3. **TEMPLATE COMPLIANCE**: Check the document against the expected template for its type. If it lacks official stamps/seals, required fields, or proper formatting → set has_valid_format = false and consider rejecting.

4. **FORENSIC SCRUTINY**: Examine at pixel level for:
   - Digital manipulation (cut-and-paste, cloning, inconsistent compression artifacts)
   - Font inconsistencies (mismatched typefaces, irregular kerning, bold/normal mix-ups)
   - Missing or counterfeit security features (holograms, watermarks, microprinting, guilloche)
   - Suspicious signatures (photocopied, stamped-on, unnaturally uniform)
   - Logic inconsistencies (future dates, impossible age, contradictory info)

5. **CONFIDENCE CALIBRATION**:
   - confidence >= 0.90 → Document passes ALL checks, all fields match, security features present and authentic, no fraud indicators
   - confidence >= 0.75 → Document looks genuine but minor uncertainties exist (slight blur, partial occlusion, some fields readable)
   - confidence >= 0.50 → Document is plausible but has notable uncertainties (blurry image, missing some expected fields, unclear stamps)
   - confidence < 0.50 → Document has significant issues — reject or needs_review

6. **REJECTION TRIGGERS** (set verdict to "rejected" and confidence appropriately):
   - ANY field mismatch with reference data (name, DOB, nationality, gender)
   - Missing or clearly counterfeit security features
   - Evidence of digital manipulation
   - Expired document (beyond reasonable period)
   - Missing critical fields (document number, issuing authority, holder name)
   - Font or format inconsistencies suggesting forgery

7. **NEEDS_REVIEW**: Only use when the image quality is genuinely too poor to make a determination (severe blur, extreme low light, massive occlusion). If you can see the document clearly, you MUST make a "verified" or "rejected" decision.

8. **FRAUD INDICATORS**: This field MUST NOT be empty unless you are 100% certain the document is authentic. Report EVERY potential issue, even subtle ones. List specific observations like "background around photo border shows compression mismatch suggesting photo replacement".

9. **FORENSIC ANALYSIS**: This is mandatory. Fill EVERY sub-field with detailed observations. If a feature is not visible, state "not visible in image" rather than leaving blank.

10. **REASONS**: Every "rejected" verdict MUST have at least 2-3 specific, actionable reasons explaining WHAT failed and WHY. "needs_review" verdicts must explain exactly what is unclear.

Remember: You are protecting the integrity of the Ghana Armed Forces. A single forged document could compromise national security. Be UNCOMPROMISING in your analysis.
PROMPT;

    private const VERIFY_THRESHOLDS = [
        'birth_certificate' => 0.75,
        'certificate'       => 0.75,
        'national_id'       => 0.80,
        'photograph'        => 0.85,
    ];

    private const REJECT_THRESHOLDS = [
        'birth_certificate' => 0.60,
        'certificate'       => 0.60,
        'national_id'       => 0.65,
        'photograph'        => 0.70,
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

                // Send email + dashboard notification (single doc auto-verified)
                $notification->documentAutoVerified($this->document);

                // 🚀 Auto-advance if all 4 required docs are now AI-verified
                $this->checkAutoAdvanceApplication($application, $notification, $eligibility);

            // ───→ REJECTED ←─────────────────────────────────────────────
            } elseif ($verdict === 'rejected' && $confidence >= $rejectThreshold) {
                $reasonText = !empty($reasons) ? implode(', ', $reasons) : 'Document did not pass AI verification.';

                // Determine if this is a disqualifying rejection (mismatch/fraud)
                // vs. non-critical (blurry/wrong type — route to admin review).
                // Disqualification + notifications are automatically handled by
                // DocumentObserver — the job only sets the status and logs.
                $crossRef   = $parsed['cross_reference'] ?? [];
                $fraudInds  = $parsed['fraud_indicators'] ?? [];
                $hasMismatch = !($crossRef['name_match'] ?? true)
                            || !($crossRef['dob_match'] ?? true)
                            || !($crossRef['nationality_match'] ?? true)
                            || !($crossRef['gender_match'] ?? true);
                $hasFraud    = is_array($fraudInds) && count($fraudInds) > 0;

                if ($hasMismatch || $hasFraud) {
                    // DocumentObserver fires on this update and handles:
                    //   - Setting application to 'rejected'
                    //   - Sending dashboard notification to applicant
                    //   - Sending disqualification email
                    //   - Notifying admins
                    $this->document->update([
                        'verification_status' => 'rejected',
                        'ai_confidence'       => $confidence,
                        'ai_verified_at'      => now(),
                        'rejection_reason'    => $reasonText,
                    ]);

                    Log::warning("Document auto-rejected — DISQUALIFYING (mismatch/fraud) — observer will handle", [
                        'doc_id'      => $this->document->id,
                        'type'        => $docType,
                        'confidence'  => $confidence,
                        'hasMismatch' => $hasMismatch,
                        'hasFraud'    => $hasFraud,
                        'reasons'     => $reasons,
                    ]);

                } else {
                    // Non-critical rejection (blurry, wrong angle, etc.) — route to admin review
                    // instead of auto-rejecting, so an admin can decide if re-upload is appropriate
                    $this->document->update([
                        'verification_status' => 'needs_review',
                        'ai_confidence'       => $confidence,
                        'ai_verified_at'      => now(),
                        'rejection_reason'    => $reasonText,
                    ]);

                    Log::info("Document routed to admin review (AI flagged but non-critical)", [
                        'doc_id'    => $this->document->id,
                        'type'      => $docType,
                        'confidence' => $confidence,
                        'reasons'   => $reasons,
                    ]);

                    // Notify admins only — applicant doesn't need to know yet
                    $notification->documentNeedsReview($this->document);
                }

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

                    // Send email + dashboard notification (document being rechecked)
                    $notification->documentRechecking($this->document, $currentAttempt, self::MAX_AI_RETRIES);
                } else {
                    // Max retries exhausted — leave as needs_review for admin
                    // Sends email + dashboard to applicant AND notifies admins
                    $notification->documentNeedsReviewApplicant($this->document, $reasons);
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

        // Notify applicant via email + dashboard (all docs verified, application advancing)
        $notification->documentsVerified($application);

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
