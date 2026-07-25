<?php

namespace App\Services\Ai\Providers;

interface AiProviderInterface
{
    public function chat(array $messages, array $options = []): array;

    public function analyzeDocument(string $filePath, string $documentType, array $context = []): array;

    /**
     * Cross-verify all applicant documents to determine if they belong to the same individual.
     *
     * @param array $documents Array of ['path' => string, 'type' => string, 'label' => string]
     * @param array $referenceData Applicant's reference data from the application form
     * @param string $prompt The cross-verification system prompt
     * @return array
     */
    public function crossVerifyDocuments(array $documents, array $referenceData, string $prompt): array;

    public function getEmbeddings(string $text): array;

    public function generateRanking(array $candidates, array $requirements): array;
}
