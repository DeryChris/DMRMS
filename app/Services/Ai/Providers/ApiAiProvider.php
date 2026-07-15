<?php

namespace App\Services\Ai\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiAiProvider implements AiProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('ai.api_service.url', 'http://localhost:8000/api/v1');
        $this->apiKey = config('ai.api_service.api_key', 'dmrms-internal-key-2026');
        $this->timeout = config('ai.api_service.timeout', 120);
    }

    public function chat(array $messages, array $options = []): array
    {
        $start = microtime(true);

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout($this->timeout)
                ->post("{$this->baseUrl}/chat/chat", [
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                Log::error('AI Service chat failed', ['status' => $response->status()]);
                return $this->error('Chat request failed', $start);
            }

            $data = $response->json();
            return $this->success($data, $start, 'chat');
        } catch (\Exception $e) {
            Log::error('AI Service chat exception: ' . $e->getMessage());
            return $this->error($e->getMessage(), $start);
        }
    }

    public function analyzeDocument(string $filePath, string $documentType, array $context = []): array
    {
        $start = microtime(true);

        try {
            $payload = [
                'document_type' => $documentType,
                'reference_data' => $context['reference_data'] ?? null,
                'document_template' => $context['document_template'] ?? null,
            ];

            if (filter_var($filePath, FILTER_VALIDATE_URL) || str_starts_with($filePath, 'http')) {
                $payload['image_url'] = $filePath;
            } else {
                $payload['image_base64'] = base64_encode(file_get_contents($filePath));
            }

            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout($this->timeout)
                ->post("{$this->baseUrl}/vision/analyze-document", $payload);

            if ($response->failed()) {
                Log::error('AI Service document analysis failed', ['status' => $response->status()]);
                return $this->error('Document analysis failed', $start);
            }

            $data = $response->json();
            return $this->success($data, $start, 'document_analysis');
        } catch (\Exception $e) {
            Log::error('AI Service document analysis exception: ' . $e->getMessage());
            return $this->error($e->getMessage(), $start);
        }
    }

    public function getEmbeddings(string $text): array
    {
        $start = microtime(true);

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(30)
                ->post("{$this->baseUrl}/embeddings", [
                    'text' => $text,
                ]);

            if ($response->failed()) {
                Log::error('AI Service embeddings failed', ['status' => $response->status()]);
                return $this->error('Embeddings request failed', $start);
            }

            $data = $response->json();
            return $this->success($data, $start, 'embeddings');
        } catch (\Exception $e) {
            Log::error('AI Service embeddings exception: ' . $e->getMessage());
            return $this->error($e->getMessage(), $start);
        }
    }

    public function generateRanking(array $candidates, array $requirements): array
    {
        $start = microtime(true);

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(60)
                ->post("{$this->baseUrl}/embeddings/rank", [
                    'candidates' => $candidates,
                    'requirements' => $requirements,
                ]);

            if ($response->failed()) {
                Log::error('AI Service ranking failed', ['status' => $response->status()]);
                return $this->error('Ranking request failed', $start);
            }

            $data = $response->json();
            return $this->success($data, $start, 'ranking');
        } catch (\Exception $e) {
            Log::error('AI Service ranking exception: ' . $e->getMessage());
            return $this->error($e->getMessage(), $start);
        }
    }

    private function success(array $data, float $start, string $type): array
    {
        return [
            'success' => true,
            'data' => $data,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'tokens_used' => 0,
            'cost' => 0.0,
        ];
    }

    private function error(string $error, float $start): array
    {
        return [
            'success' => false,
            'error' => $error,
            'processing_time' => round((microtime(true) - $start) * 1000, 2),
            'tokens_used' => 0,
            'cost' => 0.0,
        ];
    }
}
