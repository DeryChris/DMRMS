<?php

namespace App\Services\Ai;

use App\Services\Ai\Providers\AiProviderInterface;
use App\Services\Ai\Providers\FallbackProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AiGateway
{
    protected ?AiProviderInterface $provider = null;

    public function chat(string $prompt, array $context = []): array
    {
        $messages = [];

        if (!empty($context)) {
            $messages[] = ['role' => 'system', 'content' => json_encode($context)];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->getProvider()->chat($messages);
    }

    public function analyzeDocument(string $imagePath): array
    {
        $documentType = pathinfo($imagePath, PATHINFO_EXTENSION);

        return $this->getProvider()->analyzeDocument($imagePath, $documentType);
    }

    public function getEmbeddings(string $text): array
    {
        return $this->getProvider()->getEmbeddings($text);
    }

    public function generateRanking(array $candidates): array
    {
        $requirements = config('recruitment', []);

        return $this->getProvider()->generateRanking($candidates, $requirements);
    }

    protected function getProvider(): AiProviderInterface
    {
        if ($this->provider !== null) {
            return $this->provider;
        }

        $defaultProvider = config('ai.default_provider', 'openai');

        $this->provider = match ($defaultProvider) {
            'openai'  => app(OpenAiProvider::class),
            'fallback' => app(FallbackProvider::class),
            default   => $this->resolveCustomProvider($defaultProvider),
        };

        if (!$this->provider || !config('ai.fallback_enabled', true)) {
            return $this->provider ?? app(FallbackProvider::class);
        }

        try {
            $testResult = $this->provider->chat([['role' => 'user', 'content' => 'ping']]);
            if (!($testResult['success'] ?? false)) {
                throw new \RuntimeException('Provider health check failed');
            }
        } catch (\Exception $e) {
            Log::warning("Primary AI provider failed, falling back: {$e->getMessage()}");
            $this->provider = app(FallbackProvider::class);
        }

        return $this->provider;
    }

    protected function resolveCustomProvider(string $provider): AiProviderInterface
    {
        $class = config("ai.providers.{$provider}.class");

        if ($class && class_exists($class)) {
            return app($class);
        }

        throw new \InvalidArgumentException("Unknown AI provider: {$provider}");
    }

    protected function logUsage($promptType, $tokens, $cost): void
    {
        Log::channel('ai')->info('AI Gateway usage', [
            'prompt_type' => $promptType,
            'tokens'      => $tokens,
            'cost'        => $cost,
            'provider'    => config('ai.default_provider'),
            'timestamp'   => Carbon::now()->toDateTimeString(),
        ]);
    }
}
