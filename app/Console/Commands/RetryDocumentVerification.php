<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDocumentVerification;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryDocumentVerification extends Command
{
    protected $signature = 'documents:retry-verification
                            {--hours=1 : Only retry documents created within this many hours}
                            {--dry-run : List documents that would be retried without dispatching}';

    protected $description = 'Retry AI verification for documents stuck in pending/needs_review status';

    private const MAX_ATTEMPTS = 3;

    public function handle(): int
    {
        $cutoff = now()->subHours((int) $this->option('hours'));
        $dryRun = (bool) $this->option('dry-run');

        $documents = Document::whereIn('verification_status', ['pending', 'needs_review'])
            ->where('created_at', '>=', $cutoff)
            ->where('ai_verification_attempts', '<', self::MAX_ATTEMPTS)
            ->whereHas('application', fn($q) => $q->whereIn('status', [
                'submitted', 'documents_verified',
            ]))
            ->get();

        if ($documents->isEmpty()) {
            $this->info('No documents need re-verification.');
            return self::SUCCESS;
        }

        $this->info("Found {$documents->count()} document(s) to retry.");

        foreach ($documents as $document) {
            $label = str_replace('_', ' ', ucfirst($document->document_type));
            $attempts = $document->ai_verification_attempts ?? 0;

            if ($dryRun) {
                $this->line("  [DRY-RUN] Would retry: #{$document->id} ({$label}) — attempts: {$attempts}");
                continue;
            }

            ProcessDocumentVerification::dispatch($document);

            Log::info('RetryDocumentVerification: re-dispatched verification job', [
                'doc_id'   => $document->id,
                'type'     => $document->document_type,
                'attempts' => $attempts,
            ]);

            $this->line("  Dispatched retry for #{$document->id} ({$label}) — attempts: {$attempts}");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
