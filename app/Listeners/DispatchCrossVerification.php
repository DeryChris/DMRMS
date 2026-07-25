<?php

namespace App\Listeners;

use App\Events\DocumentUploaded;
use App\Jobs\CrossVerifyApplicantIdentity;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class DispatchCrossVerification
{
    private const REQUIRED_DOC_TYPES = [
        'birth_certificate', 'certificate', 'national_id', 'photograph',
    ];

    public function handle(DocumentUploaded $event): void
    {
        $document = $event->document;
        $application = $document->application;

        if (!$application) {
            return;
        }

        // Check if all 4 required document types now exist for this application
        $existingTypes = Document::where('application_id', $application->id)
            ->whereIn('document_type', self::REQUIRED_DOC_TYPES)
            ->pluck('document_type')
            ->toArray();

        $allPresent = empty(array_diff(self::REQUIRED_DOC_TYPES, $existingTypes));

        if (!$allPresent) {
            Log::info('Cross-verification: not all documents uploaded yet', [
                'application_id' => $application->id,
                'present'        => $existingTypes,
                'required'       => self::REQUIRED_DOC_TYPES,
            ]);
            return;
        }

        Log::info('All 4 required documents uploaded — dispatching cross-verification', [
            'application_id' => $application->id,
        ]);

        CrossVerifyApplicantIdentity::dispatch($application->id);
    }
}
