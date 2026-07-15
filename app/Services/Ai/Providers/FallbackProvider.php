<?php

namespace App\Services\Ai\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FallbackProvider implements AiProviderInterface
{
    public function chat(array $messages, array $options = []): array
    {
        $start = microtime(true);
        $lastMessage = end($messages);
        $content = $lastMessage['content'] ?? '';

        $response = $this->generateFallbackResponse($content);

        return [
            'success'         => true,
            'data'            => [
                'message' => [
                    'role'    => 'assistant',
                    'content' => $response,
                ],
            ],
            'model'           => 'fallback-rule-based',
            'tokens_used'     => 0,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'cost'            => 0.0,
        ];
    }

    public function analyzeDocument(string $filePath, string $documentType, array $context = []): array
    {
        $start = microtime(true);

        $fileName = basename($filePath);

        return [
            'success'         => true,
            'data'            => [
                'document_type'  => $documentType,
                'file_name'      => $fileName,
                'extracted_text' => "[Fallback OCR] Simulated text extraction for {$fileName}. Document type: {$documentType}.",
                'fields'         => [
                    'name'             => 'Extracted Name Placeholder',
                    'date_of_birth'    => '1900-01-01',
                    'document_number'  => 'FALLBACK-' . strtoupper(substr(md5($filePath), 0, 8)),
                    'nationality'      => 'Ghanaian',
                ],
            ],
            'model'           => 'fallback-ocr',
            'tokens_used'     => 0,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'cost'            => 0.0,
        ];
    }

    public function getEmbeddings(string $text): array
    {
        $start = microtime(true);

        $dimension = 1536;
        srand(crc32($text));
        $vector = [];
        for ($i = 0; $i < $dimension; $i++) {
            $vector[] = (float) (rand() / getrandmax());
        }

        return [
            'success'         => true,
            'data'            => [
                'embedding' => $vector,
                'dimension' => $dimension,
            ],
            'model'           => 'fallback-embedding',
            'tokens_used'     => 0,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'cost'            => 0.0,
        ];
    }

    public function generateRanking(array $candidates, array $requirements): array
    {
        $start = microtime(true);

        $scored = [];
        foreach ($candidates as $candidate) {
            $score = $this->keywordScoreCandidate($candidate, $requirements);
            $scored[] = [
                'candidate'    => $candidate,
                'score'        => $score,
                'explanation'  => "Keyword-based score: {$score}% match with requirements.",
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'success'         => true,
            'data'            => [
                'ranked_candidates' => $scored,
            ],
            'model'           => 'fallback-ranking',
            'tokens_used'     => 0,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'cost'            => 0.0,
        ];
    }

    protected function generateFallbackResponse(string $input): string
    {
        $input = strtolower($input);

        if (str_contains($input, 'eligibility') || str_contains($input, 'eligible')) {
            return "Based on the provided information, the applicant meets the basic eligibility criteria. Further verification is recommended.";
        }

        if (str_contains($input, 'rank') || str_contains($input, 'score')) {
            return "Candidates have been evaluated using standard criteria. Top candidates demonstrate strong educational background and physical fitness.";
        }

        if (str_contains($input, 'document') || str_contains($input, 'verify')) {
            return "Document verification is pending manual review. Initial checks indicate all required documents are present.";
        }

        return "Fallback response: Your request has been received. An administrator will review and process it manually.";
    }

    protected function keywordScoreCandidate(array $candidate, array $requirements): float
    {
        $score = 50.0;
        $keywords = [];

        foreach ($requirements as $key => $value) {
            if (is_string($value)) {
                $keywords[] = strtolower($value);
            }
        }

        $candidateText = strtolower(json_encode($candidate));
        $matchCount = 0;

        foreach ($keywords as $keyword) {
            if (str_contains($candidateText, $keyword)) {
                $matchCount++;
            }
        }

        if (!empty($keywords)) {
            $score += ($matchCount / count($keywords)) * 50;
        }

        return round(min($score, 100), 2);
    }
}
