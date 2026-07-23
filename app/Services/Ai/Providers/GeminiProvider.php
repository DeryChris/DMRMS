<?php

namespace App\Services\Ai\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $embeddingModel;
    protected int $maxTokens;
    protected float $temperature;

    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('ai.gemini.api_key');
        $this->model = config('ai.gemini.model', 'gemini-2.0-flash');
        $this->embeddingModel = config('ai.gemini.embedding_model', 'embedding-001');
        $this->maxTokens = config('ai.gemini.max_tokens', 8192);
        $this->temperature = config('ai.gemini.temperature', 0.7);
    }

    public function chat(array $messages, array $options = []): array
    {
        $start = microtime(true);

        try {
            $payload = $this->buildChatPayload($messages);

            $payload['generationConfig'] = array_merge([
                'maxOutputTokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ], $options);

            $response = Http::timeout(60)
                ->post(self::API_BASE . "/models/{$this->model}:generateContent?key={$this->apiKey}", $payload);

            if ($response->failed()) {
                Log::error('Gemini chat request failed', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->prepareErrorResponse('Chat request failed: ' . $response->body(), $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'chat');
        } catch (\Exception $e) {
            Log::error('Gemini chat exception: ' . $e->getMessage());
            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    public function analyzeDocument(string $filePath, string $documentType, array $context = []): array
    {
        $start = microtime(true);

        try {
            $imageContent = base64_encode(file_get_contents($filePath));
            $mimeType = mime_content_type($filePath) ?: 'image/jpeg';

            $referenceData = $context['reference_data'] ?? [];
            $documentTemplate = $context['document_template'] ?? '';
            $analysisPrompt = $context['analysis_prompt'] ?? '';

            $referenceSection = '';
            if (!empty($referenceData)) {
                $referenceSection = "\n\nReference data from application form:\n" . json_encode($referenceData, JSON_PRETTY_PRINT);
            }

            $templateSection = '';
            if (!empty($documentTemplate)) {
                $templateSection = "\n\nExpected {$documentType} format:\n{$documentTemplate}";
            }

            $promptBody = $analysisPrompt ?: <<<PROMPT
You are a Ghana Armed Forces document verification officer. Analyze the provided {$documentType} document image and return STRICT JSON only (no markdown, no explanation).

{
  "overall": {
    "verdict": "verified" or "rejected",
    "confidence": 0.0 to 1.0,
    "reasons": ["reason1", "reason2"]
  },
  "extracted_fields": {
    "full_name": "value or null",
    "date_of_birth": "value or null",
    "id_number": "value or null",
    "issuing_authority": "value or null",
    "date_issued": "value or null",
    "expiry_date": "value or null"
  },
  "cross_reference": {
    "name_match": true or false,
    "dob_match": true or false,
    "nationality_match": true or false
  },
  "template_validation": {
    "has_required_fields": true or false,
    "has_official_stamps": true or false,
    "has_valid_format": true or false
  },
  "fraud_indicators": ["list of suspected issues or empty array"]
}

Rules:
- Be decisive and err on the side of verification.
- Set confidence >= 0.5 if the document appears valid.
- If it looks authentic and matches reference data, mark verified.
- If clearly forged or doesn't match at all (confidence >= 0.7), mark rejected.
- When in doubt, lean toward "verified" at lower confidence.
- Extract any visible text fields from the document.
PROMPT;

            $systemPrompt = $promptBody . $referenceSection . $templateSection;

            $response = Http::timeout(120)
                ->post(self::API_BASE . "/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $imageContent,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens'  => 8192,
                        'temperature'      => 0.2,
                        'response_mime_type' => 'application/json',
                        'response_schema'  => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'overall' => [
                                    'type' => 'OBJECT',
                                    'properties' => [
                                        'verdict'    => ['type' => 'STRING'],
                                        'confidence' => ['type' => 'NUMBER'],
                                        'reasons'    => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                                    ],
                                    'required' => ['verdict', 'confidence', 'reasons'],
                                ],
                                'extracted_fields' => [
                                    'type' => 'OBJECT',
                                    'properties' => [
                                        'document_number' => ['type' => 'STRING'],
                                        'full_name'       => ['type' => 'STRING'],
                                        'date_of_birth'   => ['type' => 'STRING'],
                                        'gender'          => ['type' => 'STRING'],
                                        'issue_date'      => ['type' => 'STRING'],
                                        'expiry_date'     => ['type' => 'STRING'],
                                    ],
                                ],
                                'cross_reference' => [
                                    'type' => 'OBJECT',
                                    'properties' => [
                                        'name_match'       => ['type' => 'BOOLEAN'],
                                        'dob_match'        => ['type' => 'BOOLEAN'],
                                        'nationality_match' => ['type' => 'BOOLEAN'],
                                        'gender_match'     => ['type' => 'BOOLEAN'],
                                    ],
                                ],
                                'template_validation' => [
                                    'type' => 'OBJECT',
                                    'properties' => [
                                        'has_required_fields' => ['type' => 'BOOLEAN'],
                                        'has_official_stamps' => ['type' => 'BOOLEAN'],
                                        'has_valid_format'    => ['type' => 'BOOLEAN'],
                                    ],
                                ],
                                'fraud_indicators' => [
                                    'type' => 'ARRAY',
                                    'items' => ['type' => 'STRING'],
                                ],
                            ],
                            'required' => ['overall', 'extracted_fields', 'cross_reference', 'template_validation', 'fraud_indicators'],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini document analysis failed', ['status' => $response->status()]);
                return $this->prepareErrorResponse('Document analysis failed', $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'document_analysis');
        } catch (\Exception $e) {
            Log::error('Gemini document analysis exception: ' . $e->getMessage());
            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    public function getEmbeddings(string $text): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(30)
                ->post(self::API_BASE . "/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}", [
                    'model' => "models/{$this->embeddingModel}",
                    'content' => [
                        'parts' => [
                            ['text' => $text],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini embeddings request failed', ['status' => $response->status()]);
                return $this->prepareErrorResponse('Embeddings request failed', $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'embeddings');
        } catch (\Exception $e) {
            Log::error('Gemini embeddings exception: ' . $e->getMessage());
            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    public function generateRanking(array $candidates, array $requirements): array
    {
        $prompt = "Rank the following candidates based on these requirements: " . json_encode($requirements) . "\n\nCandidates:\n" . json_encode($candidates) . "\n\nReturn a ranked list with scores and reasoning.";

        $result = $this->chat([
            ['role' => 'system', 'content' => 'You are a recruitment ranking assistant.'],
            ['role' => 'user', 'content' => $prompt],
        ]);

        return $result;
    }

    private function buildChatPayload(array $messages): array
    {
        $contents = [];
        $systemInstruction = null;

        foreach ($messages as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = $msg['content'] ?? '';

            if ($role === 'system') {
                $systemInstruction = $content;
                continue;
            }

            $contents[] = [
                'role' => match ($role) {
                    'assistant' => 'model',
                    default => 'user',
                },
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        $payload = ['contents' => $contents];

        if ($systemInstruction) {
            $payload['system_instruction'] = [
                'parts' => [['text' => $systemInstruction]],
            ];
        }

        return $payload;
    }

    private function extractText(array $data): string
    {
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    private function extractTokens(array $data): int
    {
        $meta = $data['usageMetadata'] ?? [];
        return ($meta['promptTokenCount'] ?? 0) + ($meta['candidatesTokenCount'] ?? 0);
    }

    private function extractModel(array $data): string
    {
        return $data['modelVersion'] ?? $this->model;
    }

    protected function prepareSuccessResponse(array $data, float $start, string $promptType): array
    {
        $processingTime = round((microtime(true) - $start) * 1000, 2);
        $text = $this->extractText($data);
        $tokensUsed = $this->extractTokens($data);
        $model = $this->extractModel($data);

        $this->logUsage($promptType, $tokensUsed, 0.0);

        return [
            'success' => true,
            'data' => [
                'content' => $text,
            ],
            'model' => $model,
            'tokens_used' => $tokensUsed,
            'processing_time' => $processingTime,
            'cost' => 0.0,
        ];
    }

    protected function prepareErrorResponse(string $error, float $start): array
    {
        return [
            'success' => false,
            'error' => $error,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'tokens_used' => 0,
            'cost' => 0.0,
        ];
    }

    protected function logUsage($promptType, $tokens, $cost): void
    {
        Log::channel('ai')->info('Gemini API usage', [
            'prompt_type' => $promptType,
            'model' => $this->model,
            'tokens' => $tokens,
            'cost' => $cost,
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);
    }
}
