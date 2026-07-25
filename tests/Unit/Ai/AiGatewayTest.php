<?php

namespace Tests\Unit\Ai;

use App\Services\Ai\AiGateway;
use App\Services\Ai\Providers\AiProviderInterface;
use App\Services\Ai\Providers\FallbackProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AiGatewayTest extends TestCase
{
    private AiGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure fallback is disabled so health check doesn't run against real APIs
        config(['ai.fallback_enabled' => false]);
        config(['ai.default_provider' => 'openai']);
        config(['ai.openai.api_key' => 'test-key-12345']);

        $this->gateway = new AiGateway();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_resolves_default_provider_from_config(): void
    {
        // Use reflection to call protected getProvider
        $provider = $this->invokeGetProvider($this->gateway);

        $this->assertInstanceOf(AiProviderInterface::class, $provider);
        $this->assertInstanceOf(OpenAiProvider::class, $provider);
    }

    /** @test */
    public function it_resolves_fallback_provider_when_configured(): void
    {
        config(['ai.default_provider' => 'fallback']);

        $gateway = new AiGateway();
        $provider = $this->invokeGetProvider($gateway);

        $this->assertInstanceOf(FallbackProvider::class, $provider);
    }

    /** @test */
    public function it_caches_provider_after_first_resolution(): void
    {
        $provider1 = $this->invokeGetProvider($this->gateway);
        $provider2 = $this->invokeGetProvider($this->gateway);

        $this->assertSame($provider1, $provider2);
    }

    /** @test */
    public function it_falls_back_when_health_check_fails(): void
    {
        config(['ai.fallback_enabled' => true]);

        // Mock the HttpClient so OpenAiProvider chat fails
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $gateway = new AiGateway();
        $provider = $this->invokeGetProvider($gateway);

        // Should fall back to FallbackProvider
        $this->assertInstanceOf(FallbackProvider::class, $provider);
    }

    /** @test */
    public function it_delegates_chat_to_provider(): void
    {
        $provider = Mockery::mock(AiProviderInterface::class);
        $provider->shouldReceive('chat')
            ->once()
            ->with(Mockery::on(function ($messages) {
                return count($messages) === 2
                    && $messages[0]['role'] === 'system'
                    && $messages[1]['role'] === 'user';
            }))
            ->andReturn([
                'success' => true,
                'data' => ['content' => 'Test response'],
                'model' => 'test-model',
                'tokens_used' => 10,
                'processing_time' => 100.0,
                'cost' => 0.0,
            ]);

        $this->injectProvider($this->gateway, $provider);

        $result = $this->gateway->chat('Hello', ['type' => 'test']);
        $this->assertTrue($result['success']);
        $this->assertSame('Test response', $result['data']['content']);
    }

    /** @test */
    public function it_delegates_chat_without_context(): void
    {
        $provider = Mockery::mock(AiProviderInterface::class);
        $provider->shouldReceive('chat')
            ->once()
            ->with(Mockery::on(function ($messages) {
                return count($messages) === 1 && $messages[0]['role'] === 'user';
            }))
            ->andReturn([
                'success' => true,
                'data' => ['content' => 'No context response'],
                'model' => 'test',
                'tokens_used' => 0,
                'processing_time' => 0.0,
                'cost' => 0.0,
            ]);

        $this->injectProvider($this->gateway, $provider);

        $result = $this->gateway->chat('Just a prompt');
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_delegates_get_embeddings(): void
    {
        $provider = Mockery::mock(AiProviderInterface::class);
        $provider->shouldReceive('getEmbeddings')
            ->once()
            ->with('text to embed')
            ->andReturn([
                'success' => true,
                'data' => ['embedding' => [0.1, 0.2, 0.3], 'dimension' => 3],
                'model' => 'embedding-model',
                'tokens_used' => 5,
                'processing_time' => 10.0,
                'cost' => 0.0,
            ]);

        $this->injectProvider($this->gateway, $provider);

        $result = $this->gateway->getEmbeddings('text to embed');
        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']['embedding']);
    }

    /** @test */
    public function it_delegates_chat_with_messages(): void
    {
        $provider = Mockery::mock(AiProviderInterface::class);
        $provider->shouldReceive('chat')
            ->once()
            ->with([['role' => 'user', 'content' => 'direct message']])
            ->andReturn(['success' => true, 'data' => ['content' => 'reply'], 'model' => 'm', 'tokens_used' => 0, 'processing_time' => 0.0, 'cost' => 0.0]);

        $this->injectProvider($this->gateway, $provider);

        $result = $this->gateway->chatWithMessages([['role' => 'user', 'content' => 'direct message']]);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_delegates_generate_ranking(): void
    {
        $provider = Mockery::mock(AiProviderInterface::class);
        $provider->shouldReceive('generateRanking')
            ->once()
            ->with([['name' => 'Alice']], Mockery::any())
            ->andReturn(['success' => true, 'data' => ['ranked_candidates' => []], 'model' => 'r', 'tokens_used' => 0, 'processing_time' => 0.0, 'cost' => 0.0]);

        $this->injectProvider($this->gateway, $provider);

        $result = $this->gateway->generateRanking([['name' => 'Alice']]);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_handles_unknown_provider_by_throwing_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        config(['ai.default_provider' => 'nonexistent_provider']);

        $gateway = new AiGateway();
        $this->invokeGetProvider($gateway);
    }

    /** @test */
    public function it_uses_fallback_when_custom_provider_class_not_found(): void
    {
        config(['ai.default_provider' => 'nonexistent']);
        config(['ai.providers.nonexistent.class' => 'NonExistentProviderClass']);

        $gateway = new AiGateway();

        $this->expectException(\InvalidArgumentException::class);
        $this->invokeGetProvider($gateway);
    }

    // ──────────────────────────────────────────────
    //  Reflection helpers
    // ──────────────────────────────────────────────

    private function invokeGetProvider(AiGateway $gateway): AiProviderInterface
    {
        $ref = new \ReflectionMethod($gateway, 'getProvider');
        $ref->setAccessible(true);
        return $ref->invoke($gateway);
    }

    private function injectProvider(AiGateway $gateway, AiProviderInterface $provider): void
    {
        $ref = new \ReflectionProperty($gateway, 'provider');
        $ref->setAccessible(true);
        $ref->setValue($gateway, $provider);
    }
}
