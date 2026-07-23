<?php

namespace App\Services\Ai\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected string $embeddingModel;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->baseUrl = rtrim(config('ai.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = config('ai.openai.model', 'gpt-4-turbo');
        $this->embeddingModel = config('ai.openai.embedding_model', 'text-embedding-3-small');
        $this->maxTokens = config('ai.openai.max_tokens', 4096);
        $this->temperature = config('ai.openai.temperature', 0.7);
    }

    protected function buildClient(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::withToken($this->apiKey)->timeout(120);

        if (str_contains($this->baseUrl, 'openrouter.ai')) {
            $client = $client
                ->withHeader('HTTP-Referer', config('app.url', 'http://localhost:8000'))
                ->withHeader('X-Title', 'DMRMS');
        }

        return $client;
    }

    public function chat(array $messages, array $options = []): array
    {
        $start = microtime(true);

        try {
            $response = $this->buildClient()
                ->post("{$this->baseUrl}/chat/completions", array_merge([
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => $this->maxTokens,
                    'temperature' => $this->temperature,
                ], $options));

            if ($response->failed()) {
                Log::error('OpenAI chat request failed', ['status' => $response->status(), 'body' => $response->body()]);

                return $this->prepareErrorResponse('Chat request failed: ' . $response->body(), $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'chat');
        } catch (\Exception $e) {
            Log::error('OpenAI chat exception: ' . $e->getMessage());

            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    public function analyzeDocument(string $filePath, string $documentType, array $context = []): array
    {
        $start = microtime(true);

        try {
            $imageContent = base64_encode(file_get_contents($filePath));

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

            $messages = [
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $systemPrompt,
                        ],
                        [
                            'type'     => 'image_url',
                            'image_url' => [
                                'url' => "data:image/jpeg;base64,{$imageContent}",
                            ],
                        ],
                    ],
                ],
            ];

            $response = $this->buildClient()
                ->timeout(120)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'      => $this->model,
                    'messages'   => $messages,
                    'max_tokens' => 4096,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI document analysis failed', ['status' => $response->status()]);

                return $this->prepareErrorResponse('Document analysis failed', $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'document_analysis');
        } catch (\Exception $e) {
            Log::error('OpenAI document analysis exception: ' . $e->getMessage());

            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    public function getEmbeddings(string $text): array
    {
        $start = microtime(true);

        try {
            $response = $this->buildClient()
                ->timeout(30)
                ->post("{$this->baseUrl}/embeddings", [
                    'model' => $this->embeddingModel,
                    'input' => $text,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI embeddings request failed', ['status' => $response->status()]);

                return $this->prepareErrorResponse('Embeddings request failed', $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'embeddings');
        } catch (\Exception $e) {
            Log::error('OpenAI embeddings exception: ' . $e->getMessage());

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

    protected function prepareSuccessResponse(array $data, float $start, string $promptType): array
    {
        $processingTime = round((microtime(true) - $start) * 1000, 2);
        $tokensUsed = $data['usage']['total_tokens'] ?? 0;
        $cost = $this->calculateCost($tokensUsed, $data['model'] ?? $this->model);
        $content = $data['choices'][0]['message']['content'] ?? json_encode($data);

        $this->logUsage($promptType, $tokensUsed, $cost);

        return [
            'success'         => true,
            'data'            => ['content' => $content],
            'model'           => $data['model'] ?? $this->model,
            'tokens_used'     => $tokensUsed,
            'processing_time' => $processingTime,
            'cost'            => $cost,
        ];
    }

    protected function prepareErrorResponse(string $error, float $start): array
    {
        return [
            'success'         => false,
            'error'           => $error,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'tokens_used'     => 0,
            'cost'            => 0.0,
        ];
    }

    protected function calculateCost(int $tokens, string $model): float
    {
        $rates = [
            'gpt-4-turbo'       => ['input' => 0.01, 'output' => 0.03],
            'gpt-4'             => ['input' => 0.03, 'output' => 0.06],
            'gpt-3.5-turbo'     => ['input' => 0.001, 'output' => 0.002],
            'text-embedding-3-small' => ['input' => 0.00002, 'output' => 0.0],
        ];

        $rate = $rates[$model] ?? ['input' => 0.01, 'output' => 0.03];

        return round(($tokens / 1000) * $rate['input'], 6);
    }

    protected function logUsage($promptType, $tokens, $cost): void
    {
        Log::channel('ai')->info('OpenAI API usage', [
            'prompt_type' => $promptType,
            'model'       => $this->model,
            'tokens'      => $tokens,
            'cost'        => $cost,
            'timestamp'   => Carbon::now()->toDateTimeString(),
        ]);
    }
}
