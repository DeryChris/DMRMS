<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\Administrator;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeSoftDeletedRecords extends Command
{
    protected $signature = 'app:purge-soft-deleted {--days=90 : Permanently delete records soft-deleted longer than this many days ago}';

    protected $description = 'Permanently purge soft-deleted records older than the specified retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Purging records soft-deleted before {$cutoff->toDateTimeString()}...");

        $purgedApplicants = $this->purgeApplicants($cutoff);
        $purgedAdmins = $this->purgeAdministrators($cutoff);

        $this->info("Done. Purged {$purgedApplicants} applicant(s) and {$purgedAdmins} administrator(s).");

        return Command::SUCCESS;
    }

    private function purgeApplicants(Carbon $cutoff): int
    {
        $applicants = Applicant::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->get();

        $count = 0;

        foreach ($applicants as $applicant) {
            DB::transaction(function () use ($applicant) {
                $applicant->load('application.documents', 'voucher');

                // Delete physical document files
                if ($application = $applicant->application) {
                    foreach ($application->documents as $document) {
                        if ($document->file_path && Storage::exists($document->file_path)) {
                            Storage::delete($document->file_path);
                        }
                    }
                }

                // Delete the voucher if still linked
                if ($voucher = $applicant->voucher) {
                    $voucher->delete();
                }

                // Revoke Sanctum tokens
                $applicant->tokens()->delete();

                // Clean up orphaned audit logs
                AuditLog::where('user_id', $applicant->id)
                    ->where('user_type', 'applicant')
                    ->delete();

                // Clean up orphaned AI prompt logs
                DB::table('ai_prompt_logs')
                    ->where('user_id', $applicant->id)
                    ->where('user_type', 'applicant')
                    ->delete();

                // Hard-delete the applicant (DB cascades to: applications, documents,
                // eligibility_results, verification_codes, appointments, screening_results,
                // final_decisions, reserve_lists, applicant_corp_selections, notifications,
                // chatbot_conversations)
                $applicant->forceDelete();
            });

            $count++;
        }

        return $count;
    }

    private function purgeAdministrators(Carbon $cutoff): int
    {
        $admins = Administrator::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->get();

        $count = 0;

        foreach ($admins as $admin) {
            DB::transaction(function () use ($admin) {
                // Remove Spatie role/permission assignments
                DB::table('model_has_roles')
                    ->where('model_id', $admin->id)
                    ->where('model_type', 'App\Models\User')
                    ->delete();

                DB::table('model_has_permissions')
                    ->where('model_id', $admin->id)
                    ->where('model_type', 'App\Models\User')
                    ->delete();

                // Clean up orphaned AI usage records
                DB::table('ai_usage')
                    ->where('admin_id', $admin->id)
                    ->delete();

                // Orphaned audit logs for admin
                AuditLog::where('user_id', $admin->id)
                    ->where('user_type', 'administrator')
                    ->delete();

                // Hard-delete the admin
                $admin->forceDelete();
            });

            $count++;
        }

        return $count;
    }
}
