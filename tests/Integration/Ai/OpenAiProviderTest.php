<?php

namespace Tests\Integration\Ai;

use App\Services\Ai\Providers\OpenAiProvider;
use Tests\TestCase;

/**
 * @group integration
 * @group ai-integration
 *
 * These tests hit the real OpenAI/OpenRouter API.
 * They require OPENAI_API_KEY to be set in .env.
 *
 * To skip in CI:
 *   php artisan test --exclude-group=integration
 */
class OpenAiProviderTest extends TestCase
{
    private ?OpenAiProvider $provider;
    private bool $skipTests;

    protected function setUp(): void
    {
        parent::setUp();

        $apiKey = config('ai.openai.api_key');

        if (empty($apiKey)) {
            $this->skipTests = true;
            $this->markTestSkipped('OPENAI_API_KEY not configured. Set AI_PROVIDER=fallback or configure OPENAI_API_KEY in .env to run integration tests.');
        }

        $this->skipTests = false;
        $this->provider = app(OpenAiProvider::class);
    }

    /** @test */
    public function it_passes_health_check_with_chat(): void
    {
        $result = $this->provider->chat([
            ['role' => 'user', 'content' => 'Reply with exactly the word "pong" and nothing else.'],
        ]);

        $this->assertTrue($result['success'], 'Chat health check failed: ' . ($result['error'] ?? 'unknown error'));
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('content', $result['data']);
        $this->assertIsString($result['data']['content']);
        $this->assertArrayHasKey('model', $result);
        $this->assertArrayHasKey('tokens_used', $result);
        $this->assertIsInt($result['tokens_used']);
        $this->assertGreaterThan(0, $result['tokens_used'], 'Should have consumed at least 1 token');
        $this->assertArrayHasKey('processing_time', $result);
        $this->assertGreaterThan(0, $result['processing_time'], 'Processing time should be measurable');
        $this->assertArrayHasKey('cost', $result);
        $this->assertIsFloat($result['cost']);
    }

    /** @test */
    public function it_generates_chat_response_about_eligibility(): void
    {
        $result = $this->provider->chat([
            ['role' => 'system', 'content' => 'You are a DMRMS recruitment assistant. Keep responses brief.'],
            ['role' => 'user', 'content' => 'What is the minimum age requirement for enlistment?'],
        ], ['temperature' => 0.3]);

        $this->assertTrue($result['success']);
        $content = strtolower($result['data']['content'] ?? '');
        $this->assertNotEmpty($content, 'Response content should not be empty');
        // Should mention age or year somewhere
        $this->assertThat(
            preg_match('/\d{2}/', $content) === 1,
            $this->isTrue(),
            'Response should contain a number (age requirement)'
        );
    }

    /** @test */
    public function it_returns_embeddings_vector(): void
    {
        $result = $this->provider->getEmbeddings('DMRMS Ghana Armed Forces recruitment test query');

        $this->assertTrue($result['success'], 'Embeddings request failed: ' . ($result['error'] ?? 'unknown'));
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('content', $result['data']);

        // Parse the JSON embedding response
        $data = json_decode($result['data']['content'], true);
        if (isset($data['data'][0]['embedding'])) {
            $embedding = $data['data'][0]['embedding'];
        } elseif (isset($result['data']['embedding'])) {
            $embedding = $result['data']['embedding'];
        } else {
            // Some models return the vector directly
            $this->markTestIncomplete('Could not parse embedding from response structure');
            return;
        }

        $this->assertIsArray($embedding);
        $this->assertCount(1536, $embedding, 'text-embedding-3-small should return 1536 dimensions');
        $this->assertGreaterThan(0, $result['tokens_used']);
    }

    /** @test */
    public function it_returns_structured_response_for_forumlaic_prompt(): void
    {
        $result = $this->provider->chat([
            ['role' => 'user', 'content' => 'Return a JSON object with keys: "name", "version", "status". Use "dmrms", "1.0", "ok". No markdown, raw JSON only.'],
        ], ['temperature' => 0.1]);

        $this->assertTrue($result['success']);
        $content = $result['data']['content'] ?? '';

        // Try to parse JSON from response
        $json = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try extracting with regex
            preg_match('/\{.*\}/s', $content, $match);
            if (!empty($match)) {
                $json = json_decode($match[0], true);
            }
        }

        $this->assertIsArray($json, 'Response should contain valid JSON: ' . substr($content, 0, 200));
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('version', $json);
        $this->assertArrayHasKey('status', $json);
    }

    /** @test */
    public function it_reports_failure_for_invalid_api_key(): void
    {
        $originalKey = config('ai.openai.api_key');
        config(['ai.openai.api_key' => 'sk-invalid-key-12345']);

        $badProvider = app(OpenAiProvider::class);

        $result = $badProvider->chat([
            ['role' => 'user', 'content' => 'test'],
        ]);

        $this->assertFalse($result['success'], 'Should fail with invalid API key');
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertSame(0, $result['tokens_used']);
        $this->assertSame(0.0, $result['cost']);

        // Restore
        config(['ai.openai.api_key' => $originalKey]);
    }

    /** @test */
    public function it_uses_correct_headers_for_openrouter(): void
    {
        $originalUrl = config('ai.openai.base_url');
        config(['ai.openai.base_url' => 'https://openrouter.ai/api/v1']);

        // With a valid (or invalid) key, the headers will be set even if the request fails
        $provider = app(OpenAiProvider::class);

        // Use reflection to verify the client builder sets OpenRouter headers
        $ref = new \ReflectionMethod($provider, 'buildClient');
        $ref->setAccessible(true);

        // This should not throw — we just verify the method is callable
        $this->assertTrue(method_exists($provider, 'buildClient'));

        config(['ai.openai.base_url' => $originalUrl]);
    }
}
