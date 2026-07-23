<?php

namespace App\Console\Commands;

use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupDrafts extends Command
{
    protected $signature = 'app:cleanup-drafts
        {--days=30 : Delete draft documents older than this many days}
        {--dry-run : Preview which documents would be deleted without actually deleting}';

    protected $description = 'Delete draft documents that have been abandoned for more than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Looking for draft documents older than {$days} days (before {$cutoff->toDateString()})...");

        $expiredDrafts = Document::where('is_draft', true)
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = $expiredDrafts->count();

        if ($count === 0) {
            $this->info('No expired draft documents found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} expired draft document(s).");

        if ($dryRun) {
            $this->warn('DRY RUN — no documents will be deleted.');
            $this->table(
                ['ID', 'Type', 'Applicant', 'Created At', 'File'],
                $expiredDrafts->map(fn($d) => [
                    $d->id,
                    $d->document_type,
                    $d->application?->applicant?->name ?? 'N/A',
                    $d->created_at?->toDateString(),
                    $d->file_path,
                ])
            );
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $deleted = 0;
        foreach ($expiredDrafts as $doc) {
            try {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
                $deleted++;
            } catch (\Exception $e) {
                Log::error('Failed to delete draft document', [
                    'doc_id' => $doc->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to delete document ID {$doc->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully deleted {$deleted} expired draft document(s).");

        Log::info('CleanupDrafts completed', [
            'found' => $count,
            'deleted' => $deleted,
            'cutoff_days' => $days,
        ]);

        return Command::SUCCESS;
    }
}
