<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Cycle;
use App\Models\Faq;
use App\Models\SystemSetting;

class AiContextService
{
    public function chatMessages(?Applicant $applicant, string $message, array $history = []): array
    {
        $systemPrompt = $this->buildSystemPrompt($applicant);

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        if (!empty($history)) {
            $messages = array_merge($messages, $history);
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        return $messages;
    }

    public function buildSystemContext(): string
    {
        $parts = [];
        $cycle = Cycle::where('status', 'active')->first();
        $cycleReqs = $cycle?->requirements ?? [];

        $parts[] = '=== ABOUT DMRMS ===';
        $parts[] = 'DMRMS (Defence Manpower Recruitment Management System) is the official online platform for Ghana Armed Forces (GAF) recruitment.';
        $parts[] = 'The entire application process is free. Report anyone asking for payment to GAF authorities.';

        $parts[] = '';
        $parts[] = '=== ELIGIBILITY REQUIREMENTS ===';
        $nationality = $cycleReqs['nationality'] ?? config('recruitment.nationality', 'Ghanaian');
        $parts[] = "Nationality: {$nationality} (citizens by birth only)";

        $ageMin = $cycleReqs['age_min'] ?? SystemSetting::getValue('min_age') ?? config('recruitment.age_min', 18);
        $ageMax = $cycleReqs['age_max'] ?? SystemSetting::getValue('max_age') ?? null;

        if ($ageMax) {
            $parts[] = "Age: {$ageMin}-{$ageMax} years";
            $parts[] = 'Age limits vary by category:';
            foreach (['regular' => 25, 'tradesmen' => 27, 'officer' => 30] as $cat => $defaultMax) {
                $catMax = config("recruitment.age_max_{$cat}", $defaultMax);
                $parts[] = "- {$cat}: {$ageMin}-{$catMax} years";
            }
        } else {
            foreach (['regular' => 25, 'tradesmen' => 27, 'officer' => 30] as $cat => $defaultMax) {
                $catMax = config("recruitment.age_max_{$cat}", $defaultMax);
                $parts[] = "- {$cat}: {$ageMin}-{$catMax} years";
            }
        }

        $heightMale = $cycleReqs['height_min_male'] ?? SystemSetting::getValue('min_height_male') ?? config('recruitment.height_min_male', 1.65);
        $heightFemale = $cycleReqs['height_min_female'] ?? SystemSetting::getValue('min_height_female') ?? config('recruitment.height_min_female', 1.58);
        $parts[] = "Minimum height: {$heightMale}m (male), {$heightFemale}m (female)";

        $parts[] = 'Education: minimum SSCE/WASSCE with passes in core subjects';
        $eduLevels = $cycleReqs['education_levels'] ?? config('recruitment.education_levels', ['SHS', 'Diploma', 'HND', 'Degree', 'Masters', 'PhD']);
        $parts[] = 'Education levels accepted: ' . implode(', ', $eduLevels);

        $conditions = config('recruitment.health_conditions', []);
        if (!empty($conditions)) {
            $parts[] = 'Health conditions to declare: ' . implode(', ', $conditions);
        }

        $regions = config('recruitment.regions', []);
        if (!empty($regions)) {
            $parts[] = 'Regions: ' . implode(', ', $regions);
        }

        $parts[] = '';
        $parts[] = '=== VOUCHER INFORMATION ===';
        $parts[] = 'Vouchers must be purchased BEFORE applying. Prices:';
        if ($cycle && $cycle->voucher_price) {
            $parts[] = "Current active cycle voucher: GHS " . number_format($cycle->voucher_price, 2);
        }
        foreach (config('recruitment.voucher_costs', []) as $cat => $cost) {
            $parts[] = "- {$cat}: GHS {$cost}";
        }
        $parts[] = 'Vouchers can be purchased at designated banks or recruitment centers nationwide.';
        $parts[] = 'Each voucher is unique and tied to your email address.';

        $parts[] = '';
        $parts[] = '=== REQUIRED DOCUMENTS ===';
        $parts[] = '- Birth certificate';
        $parts[] = '- National ID (Ghana Card)';
        $parts[] = '- WASSCE/SSCE certificate (or higher education certificate)';
        $parts[] = '- Passport photograph (450x540 pixels, white background)';
        $parts[] = '- Medical report';
        $parts[] = '- Police clearance';
        $parts[] = 'Accepted formats: PDF, JPEG. Maximum file size: 2MB per document (5MB for photos).';

        $parts[] = '';
        $parts[] = '=== APPLICATION PROCESS ===';
        $stages = config('recruitment.application_stages', []);
        if (!empty($stages)) {
            $parts[] = 'Stages: ' . implode(' -> ', $stages);
        }
        $parts[] = 'The form takes approximately 20-30 minutes to complete.';
        $parts[] = 'Once submitted, an application cannot be edited.';
        $parts[] = 'Results are published on the portal; notifications are sent via email and SMS.';
        $parts[] = 'Appeals can be submitted within 14 days of a rejection decision.';
        $parts[] = 'Screening includes: medical examination, fitness test, and oral interview.';
        $parts[] = 'Screening is conducted at designated GAF recruitment centers nationwide.';

        $parts[] = '';
        $parts[] = '=== ACTIVE RECRUITMENT CYCLE ===';
        if ($cycle) {
            $parts[] = "Name: {$cycle->name}";
            $parts[] = "Code: {$cycle->cycle_code}";
            $parts[] = "Period: {$cycle->start_date->format('d M Y')} to {$cycle->end_date->format('d M Y')}";
            $parts[] = "Application Deadline: {$cycle->application_deadline->format('d M Y H:i')}";
            $parts[] = "Total Vacancies: {$cycle->total_vacancies}";
            $parts[] = "Voucher Price: GHS " . number_format($cycle->voucher_price, 2);
            if (!empty($cycleReqs)) {
                foreach ($cycleReqs as $key => $value) {
                    if (is_scalar($value)) {
                        $parts[] = "- {$key}: {$value}";
                    }
                }
            }
        } else {
            $parts[] = 'There is currently no active recruitment cycle. Please check back later.';
        }

        $parts[] = '';
        $parts[] = '=== FREQUENTLY ASKED QUESTIONS ===';
        $faqs = Faq::where('is_published', true)->orderBy('sort_order')->get();
        if ($faqs->isNotEmpty()) {
            foreach ($faqs as $faq) {
                $parts[] = "[{$faq->category}] Q: {$faq->question}";
                $parts[] = "A: {$faq->answer}";
            }
        } else {
            $parts[] = 'No FAQs available at this time.';
        }

        return implode("\n", $parts);
    }

    public function buildUserContext(Applicant $applicant): string
    {
        $parts = [];
        $parts[] = '=== APPLICANT PERSONAL DATA ===';
        $parts[] = "Name: {$applicant->name}";
        $parts[] = "Email: {$applicant->email}";
        $parts[] = "Phone: {$applicant->contact_number}";
        $parts[] = "Date of Birth: " . ($applicant->date_of_birth ? $applicant->date_of_birth->format('d M Y') : 'Not provided');
        $parts[] = "Gender: " . ucfirst($applicant->gender ?? 'Not provided');
        $parts[] = "Region: " . ($applicant->region ?? 'Not provided');
        $parts[] = "Nationality: " . ($applicant->nationality ?? 'Not provided');

        $voucher = $applicant->voucher;
        if ($voucher) {
            $parts[] = '';
            $parts[] = '--- Voucher ---';
            $parts[] = "Number: {$voucher->voucher_number}";
            $parts[] = "Status: {$voucher->status}";
            if ($voucher->cycle) {
                $parts[] = "Cycle: {$voucher->cycle->name}";
            }
        }

        $application = $applicant->application;
        if ($application) {
            $parts[] = '';
            $parts[] = '--- Application ---';
            $parts[] = "GAF ID: {$application->gaf_id}";
            $parts[] = "Status: {$application->status}";
            $parts[] = "Current Step: {$application->current_step}";
            $parts[] = "Submitted: " . ($application->submitted_at ? $application->submitted_at->format('d M Y H:i') : 'Not yet submitted');

            if ($application->cycle) {
                $parts[] = "Recruitment Cycle: {$application->cycle->name} ({$application->cycle->cycle_code})";
            }

            $parts[] = "Height: " . ($application->height ? $application->height . ' cm' : 'Not provided');
            $parts[] = "Weight: " . ($application->weight ? $application->weight . ' kg' : 'Not provided');
            $parts[] = "Education: " . ($application->education_level ?? 'Not provided');
            $parts[] = "Institution: " . ($application->institution_name ?? 'Not provided');

            $docs = $application->documents;
            if ($docs->isNotEmpty()) {
                $parts[] = '';
                $parts[] = '--- Documents ---';
                foreach ($docs as $doc) {
                    $status = $doc->verification_status ?? 'pending';
                    $remarks = $doc->remarks ? " (Remarks: {$doc->remarks})" : '';
                    $parts[] = "- {$doc->document_type}: {$status}{$remarks}";
                }
            }

            $eligibility = $application->eligibilityResult;
            if ($eligibility) {
                $parts[] = '';
                $parts[] = '--- Eligibility Check ---';
                $parts[] = "Overall Status: {$eligibility->overall_status}";
                $parts[] = "Age Check: " . ($eligibility->age_check !== null ? ($eligibility->age_check ? 'Passed' : 'Failed') : 'Pending');
                $parts[] = "Nationality Check: " . ($eligibility->nationality_check !== null ? ($eligibility->nationality_check ? 'Passed' : 'Failed') : 'Pending');
                $parts[] = "Education Check: " . ($eligibility->education_check !== null ? ($eligibility->education_check ? 'Passed' : 'Failed') : 'Pending');
                $parts[] = "Height Check: " . ($eligibility->height_check !== null ? ($eligibility->height_check ? 'Passed' : 'Failed') : 'Pending');
                $parts[] = "Criminal Check: " . ($eligibility->criminal_check !== null ? ($eligibility->criminal_check ? 'Passed' : 'Failed') : 'Pending');
                $parts[] = "Document Check: " . ($eligibility->document_check !== null ? ($eligibility->document_check ? 'Passed' : 'Failed') : 'Pending');
                if ($eligibility->rejection_reasons) {
                    $reasons = is_array($eligibility->rejection_reasons) ? implode('; ', $eligibility->rejection_reasons) : $eligibility->rejection_reasons;
                    $parts[] = "Rejection Reasons: {$reasons}";
                }
            }

            $screening = $application->screeningResult;
            if ($screening) {
                $parts[] = '';
                $parts[] = '--- Screening Results ---';
                $parts[] = "Medical Result: " . ($screening->medical_result ?? 'Pending');
                $parts[] = "Fitness Result: " . ($screening->fitness_result ?? 'Pending') . ($screening->fitness_score !== null ? " (Score: {$screening->fitness_score})" : '');
                $parts[] = "Interview Result: " . ($screening->interview_result ?? 'Pending') . ($screening->interview_score !== null ? " (Score: {$screening->interview_score})" : '');
                $parts[] = "Overall Status: {$screening->overall_status}";
            }

            $appointment = $application->appointment;
            if ($appointment) {
                $parts[] = '';
                $parts[] = '--- Screening Appointment ---';
                $parts[] = "Date: {$appointment->scheduled_date}";
                $parts[] = "Time: {$appointment->scheduled_time}";
                $parts[] = "Venue: {$appointment->venue}";
                $parts[] = "Status: {$appointment->status}";
            }

            $decision = $application->finalDecision;
            if ($decision) {
                $parts[] = '';
                $parts[] = '--- Final Decision ---';
                $parts[] = "Decision: {$decision->decision}";
                if ($decision->decision_reason) {
                    $parts[] = "Reason: {$decision->decision_reason}";
                }
                if ($decision->decision_date) {
                    $parts[] = "Date: {$decision->decision_date->format('d M Y')}";
                }
                if ($decision->reporting_code) {
                    $parts[] = "Reporting Code: {$decision->reporting_code}";
                }
            }
        }

        $notifications = $applicant->notifications()
            ->whereNull('read_at')
            ->latest()
            ->take(5)
            ->get();

        if ($notifications->isNotEmpty()) {
            $parts[] = '';
            $parts[] = '--- Recent Unread Notifications ---';
            foreach ($notifications as $notif) {
                $date = $notif->sent_at ? $notif->sent_at->format('d M Y') : '';
                $parts[] = "- {$notif->title}: {$notif->message} ({$date})";
            }
        }

        return implode("\n", $parts);
    }

    private function buildSystemPrompt(?Applicant $applicant): string
    {
        $prompt = 'You are a helpful recruitment assistant for the Ghana Armed Forces (GAF) Defence Manpower Recruitment Management System (DMRMS).';

        $prompt .= "\n\n";
        $prompt .= 'Below is the SYSTEM KNOWLEDGE BASE containing official DMRMS information. Answer questions ONLY based on this data.';
        $prompt .= "\n\n";
        $prompt .= $this->buildSystemContext();

        if ($applicant) {
            $applicant->load([
                'voucher.cycle',
                'application.cycle',
                'application.documents',
                'application.eligibilityResult',
                'application.screeningResult',
                'application.appointment',
                'application.finalDecision',
            ]);

            $prompt .= "\n\n";
            $prompt .= 'Below is the APPLICANT DATA for the logged-in user. Use this to answer personal questions about their application status, documents, appointments, etc.';
            $prompt .= "\n";
            $prompt .= $this->buildUserContext($applicant);
        }

        $prompt .= "\n\n";
        $prompt .= '=== INSTRUCTIONS ===';
        $prompt .= "\n";
        $prompt .= '1. Answer ONLY based on the SYSTEM KNOWLEDGE BASE and APPLICANT DATA provided above. Do NOT use any external knowledge or your training data.';
        $prompt .= "\n";
        $prompt .= '2. If the answer is not in the provided context, respond with: "I don\'t have that information in the system."';
        $prompt .= "\n";
        $prompt .= '3. For personal questions (status, documents, appointments, eligibility), refer to the APPLICANT DATA section.';
        $prompt .= "\n";
        $prompt .= '4. Be concise, professional, and helpful. If the applicant\'s name is available, use it when addressing them.';
        $prompt .= "\n";
        $prompt .= '5. If the user asks about topics outside DMRMS recruitment, politely redirect them.';
        $prompt .= "\n";
        $prompt .= '6. Never fabricate deadlines, prices, or requirements. If the active cycle data shows no current cycle, state that clearly.';

        return $prompt;
    }
}
