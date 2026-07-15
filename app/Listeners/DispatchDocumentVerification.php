<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Events\DocumentUploaded;
use App\Jobs\ProcessDocumentVerification;

class DispatchDocumentVerification
{
    public function handle(DocumentUploaded|ApplicationSubmitted $event): void
    {
        $documents = match (true) {
            $event instanceof DocumentUploaded => collect([$event->document]),
            $event instanceof ApplicationSubmitted => $event->application->documents,
        };

        foreach ($documents as $document) {
            ProcessDocumentVerification::dispatch($document);
        }
    }
}
