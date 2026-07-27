<?php

namespace App\Services\Ai\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Get the model to use for a given task.
     *
     * Falls back through:
     *   1. Task-specific config (ai.openai.task_models.{task})
     *   2. Default model (ai.openai.model)
     *   3. The class-level $this->model property
     */
    protected function getModel(string $task): string
    {
        return config("ai.openai.task_models.{$task}", $this->model) ?? $this->model;
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
        if (!$this->checkBudget()) {
            return [
                'success' => false,
                'error'   => 'AI budget exceeded — daily or monthly limit reached. Contact admin or wait for reset.',
            ];
        }

        $start = microtime(true);
        $model = $this->getModel('chat');

        try {
            $response = $this->buildClient()
                ->post("{$this->baseUrl}/chat/completions", array_merge([
                    'model'       => $model,
                    'messages'    => $messages,
                    'max_tokens'  => $this->maxTokens,
                    'temperature' => $this->temperature,
                ], $options));

            if ($response->failed()) {
                Log::error('OpenAI chat request failed', ['status' => $response->status(), 'body' => $response->body()]);

                return $this->prepareErrorResponse('Chat request failed: ' . $response->body(), $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'chat', $model);
        } catch (\Exception $e) {
            Log::error('OpenAI chat exception: ' . $e->getMessage());

            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    public function analyzeDocument(string $filePath, string $documentType, array $context = []): array
    {
        if (!$this->checkBudget()) {
            return ['success' => false, 'error' => 'AI budget exceeded'];
        }

        $start = microtime(true);
        $model = $this->getModel('document_analysis');

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
Analyze this {$documentType} image for Ghana Armed Forces. Return STRICT JSON only (no markdown).

{
  "overall": {
    "verdict": "verified|rejected|needs_review",
    "confidence": 0.0-1.0,
    "reasons": ["reason1","reason2"]
  },
  "extracted_fields": {
    "full_name": "value or null",
    "date_of_birth": "value or null",
    "id_number": "value or null",
    "issuing_authority": "value or null",
    "date_issued": "value or null",
    "expiry_date": "value or null",
    "gender": "Male,Female,null",
    "nationality": "value or null"
  },
  "cross_reference": {
    "name_match": true/false,
    "dob_match": true/false,
    "nationality_match": true/false,
    "gender_match": true/false
  },
  "template_validation": {
    "has_required_fields": true/false,
    "has_official_stamps": true/false,
    "has_valid_format": true/false,
    "has_security_features": true/false,
    "font_consistent": true/false
  },
  "fraud_indicators": ["issue1","issue2"]
}

Rules:
- confidence≥0.50→verified, ≥0.30→needs_review, <0.30→rejected
- REJECT on field mismatch vs reference data
- REJECT if missing stamps/security features
- REJECT on digital manipulation evidence
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
                                'url'    => "data:image/jpeg;base64,{$imageContent}",
                                'detail' => 'low',
                            ],
                        ],
                    ],
                ],
            ];

            $response = $this->buildClient()
                ->timeout(120)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'      => $model,
                    'messages'   => $messages,
                    'max_tokens' => 1024,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI document analysis failed', ['status' => $response->status(), 'body' => $response->body()]);

                return $this->prepareErrorResponse('Document analysis failed: ' . $response->body(), $start);
            }

            $data = $response->json();

            return $this->prepareSuccessResponse($data, $start, 'document_analysis', $model);
        } catch (\Exception $e) {
            Log::error('OpenAI document analysis exception: ' . $e->getMessage());

            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    public function crossVerifyDocuments(array $documents, array $referenceData, string $prompt): array
    {
        if (!$this->checkBudget()) {
            return ['success' => false, 'error' => 'AI budget exceeded'];
        }

        $start = microtime(true);
        $model = $this->getModel('cross_verify');

        try {
            // Build content parts: text prompt + one image_url per document
            $content = [
                [
                    'type' => 'text',
                    'text' => $prompt . "\n\nReference Data:\n" . json_encode($referenceData, JSON_PRETTY_PRINT),
                ],
            ];

            foreach ($documents as $doc) {
                $imageContent = base64_encode(file_get_contents($doc['path']));
                $label = $doc['label'] ?? $doc['type'] ?? 'document';

                // Add a label text part before each image
                $content[] = [
                    'type' => 'text',
                    'text' => "--- Document: {$label} ---",
                ];
                $content[] = [
                    'type'     => 'image_url',
                    'image_url' => [
                        'url'    => "data:image/jpeg;base64,{$imageContent}",
                        'detail' => 'low',
                    ],
                ];
            }

            $messages = [
                ['role' => 'user', 'content' => $content],
            ];

            $response = $this->buildClient()
                ->timeout(120)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'      => $model,
                    'messages'   => $messages,
                    'max_tokens' => 1024,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI cross-verify documents failed', ['status' => $response->status()]);
                return $this->prepareErrorResponse('Cross-verify documents failed', $start);
            }

            $data = $response->json();
            $result = $this->prepareSuccessResponse($data, $start, 'cross_verify_documents', $model);

            // Parse the JSON from the response content
            $content = $result['data']['content'] ?? '';
            $parsed = $this->extractCrossVerifyJson($content);

            return [
                'success' => true,
                'data'    => $parsed ?: ['raw_content' => $content],
                'model'   => $result['model'] ?? $model,
                'tokens_used' => $result['tokens_used'] ?? 0,
                'processing_time' => $result['processing_time'] ?? 0,
                'cost'    => $result['cost'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI cross-verify documents exception: ' . $e->getMessage());
            return $this->prepareErrorResponse($e->getMessage(), $start);
        }
    }

    protected function extractCrossVerifyJson(string $text): ?array
    {
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);

        if (preg_match('/\{.*\}/s', $text, $match)) {
            $decoded = json_decode($match[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    public function getEmbeddings(string $text): array
    {
        if (!$this->checkBudget()) {
            return ['success' => false, 'error' => 'AI budget exceeded'];
        }

        $start = microtime(true);
        $model = $this->getModel('embeddings') ?: $this->embeddingModel;

        try {
            $response = $this->buildClient()
                ->timeout(30)
                ->post("{$this->baseUrl}/embeddings", [
                    'model' => $model,
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

        // Override the model reported in the result to reflect the ranking-specific model
        $rankingModel = $this->getModel('ranking');
        if ($rankingModel !== $this->getModel('chat')) {
            $result['model'] = $rankingModel;
        }

        return $result;
    }

    protected function prepareSuccessResponse(array $data, float $start, string $promptType, ?string $modelOverride = null): array
    {
        $processingTime = round((microtime(true) - $start) * 1000, 2);
        $usedModel = $data['model'] ?? $modelOverride ?? $this->model;
        $tokensUsed = $data['usage']['total_tokens'] ?? 0;
        $cost = $this->calculateCost($tokensUsed, $usedModel);
        $content = $data['choices'][0]['message']['content'] ?? json_encode($data);

        $this->logUsage($promptType, $tokensUsed, $cost);
        $this->trackSpend($cost);

        return [
            'success'         => true,
            'data'            => ['content' => $content],
            'model'           => $usedModel,
            'tokens_used'     => $tokensUsed,
            'processing_time' => $processingTime,
            'cost'            => $cost,
        ];
    }

    /**
     * Check daily/monthly budget before making an API call.
     * Returns true if under budget, false if exceeded.
     */
    protected function checkBudget(): bool
    {
        $dailyBudget = (float) env('AI_DAILY_BUDGET', 0);
        $monthlyBudget = (float) env('AI_MONTHLY_BUDGET', 0);

        if ($dailyBudget <= 0 && $monthlyBudget <= 0) {
            return true; // No budget caps configured
        }

        $dailySpent = (float) Cache::get('ai_daily_spent_' . now()->format('Y-m-d'), 0);
        $monthlySpent = (float) Cache::get('ai_monthly_spent_' . now()->format('Y-m'), 0);

        if ($dailyBudget > 0 && $dailySpent >= $dailyBudget) {
            Log::warning('AI budget exceeded: daily limit reached', [
                'spent'  => $dailySpent,
                'limit'  => $dailyBudget,
                'date'   => now()->format('Y-m-d'),
            ]);
            return false;
        }

        if ($monthlyBudget > 0 && $monthlySpent >= $monthlyBudget) {
            Log::warning('AI budget exceeded: monthly limit reached', [
                'spent'  => $monthlySpent,
                'limit'  => $monthlyBudget,
                'month'  => now()->format('Y-m'),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Record spend in cache-based counters (stored in millicents for precision).
     */
    protected function trackSpend(float $cost): void
    {
        if ($cost <= 0) {
            return;
        }

        $millicents = (int) round($cost * 1000);
        Cache::increment('ai_daily_spent_' . now()->format('Y-m-d'), $millicents);
        Cache::increment('ai_monthly_spent_' . now()->format('Y-m'), $millicents);
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
            'gpt-4o-mini'       => ['input' => 0.00015, 'output' => 0.0006],
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
