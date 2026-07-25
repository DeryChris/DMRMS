<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Observers\DocumentObserver;
use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReEvaluateDisqualifiedDocuments extends Command
{
    protected $signature = 'documents:re-evaluate-disqualified
        {--dry-run : Only report what would change, do not update}
        {--applicant-id= : Only re-evaluate a specific applicant}
        {--document-id= : Only re-evaluate a specific document}';

    protected $description = 'Re-evaluate all rejected documents against the current disqualification rules. Any document with disqualifying reasons (mismatch/fraud) will trigger automatic disqualification.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $applicantId = $this->option('applicant-id');
        $documentId = $this->option('document-id');

        // Build query: rejected docs whose applications are NOT already in terminal state
        $query = Document::where('verification_status', 'rejected')
            ->whereHas('application', function ($q) {
                $q->whereNotIn('status', ['rejected', 'disqualified', 'selected', 'recruited']);
            });

        if ($applicantId) {
            $query->whereHas('application', fn($q) => $q->where('applicant_id', $applicantId));
        }
        if ($documentId) {
            $query->where('id', $documentId);
        }

        $total = $query->count();
        $this->line("Found {$total} rejected document(s) to evaluate.");

        if ($total === 0) {
            $this->info('Nothing to do.');
            return Command::SUCCESS;
        }

        $disqualified = 0;
        $skipped = 0;
        $errors = 0;

        $query->chunk(50, function ($documents) use ($dryRun, &$disqualified, &$skipped, &$errors) {
            foreach ($documents as $document) {
                try {
                    if (!DocumentObserver::isDisqualifyingRejection($document)) {
                        $skipped++;
                        continue;
                    }

                    $application = $document->application;
                    $applicant = $application?->applicant;

                    $this->line(
                        "  [DISQUALIFYING] Doc #{$document->id} ({$document->document_type})"
                        . " — App #{$application->id} ({$application->gaf_id})"
                        . " — {$applicant?->first_name} {$applicant?->last_name}"
                    );

                    if (!$dryRun) {
                        // Disqualify
                        $application->update(['status' => 'rejected']);

                        // Notify applicant
                        if ($applicant) {
                            $notification = app(NotificationService::class);
                            $docTypeLabel = str_replace('_', ' ', ucfirst($document->document_type));
                            $reasonText = $document->rejection_reason ?? 'No specific reason provided.';

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
                                Log::error("Disqualification email failed from re-eval command", [
                                    'applicant' => $applicant->email,
                                    'error'     => $e->getMessage(),
                                ]);
                            }

                            $notification->notifyAdminsByRole(
                                ['admin', 'super_admin'],
                                'document_disqualified',
                                "Applicant Disqualified — Re-evaluation — {$docTypeLabel}",
                                "{$application->gaf_id}: {$applicant->name} disqualified during re-evaluation — {$docTypeLabel} rejected (disqualifying). {$reasonText}"
                            );
                        }

                        Log::info('Applicant disqualified during re-evaluation', [
                            'document_id'    => $document->id,
                            'application_id' => $application->id,
                            'reason'         => $document->rejection_reason,
                        ]);
                    }

                    $disqualified++;
                } catch (\Exception $e) {
                    $this->error("  Error processing doc #{$document->id}: {$e->getMessage()}");
                    Log::error('Re-evaluation error', [
                        'document_id' => $document->id,
                        'error'       => $e->getMessage(),
                    ]);
                    $errors++;
                }
            }
        });

        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Disqualified', $disqualified],
                ['Skipped (fixable)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN — No changes were made. Re-run without --dry-run to apply.');
        } else {
            $this->info("Done. {$disqualified} applicant(s) disqualified, {$skipped} skipped (fixable), {$errors} error(s).");
        }

        return Command::SUCCESS;
    }
}
