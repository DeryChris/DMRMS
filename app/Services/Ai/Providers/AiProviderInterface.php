<?php

namespace App\Services\Ai\Providers;

interface AiProviderInterface
{
    public function chat(array $messages, array $options = []): array;

    public function analyzeDocument(string $filePath, string $documentType, array $context = []): array;

    public function getEmbeddings(string $text): array;

    public function generateRanking(array $candidates, array $requirements): array;
}
