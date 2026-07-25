<?php

namespace Tests\Feature\Ai;

use App\Http\Controllers\Api\V1\AiController;
use App\Models\AiUsage;
use App\Services\Ai\AiGateway;
use App\Services\AiContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

/**
 * Tests for the AiController.
 *
 * Instantiates the controller directly with mocked dependencies.
 * The ai_usage table is created in setUp because the controller's
 * logUsage() method writes to it on every action.
 */
class AiControllerTest extends TestCase
{
    private AiGateway|Mockery\MockInterface $aiGateway;
    private AiContextService|Mockery\MockInterface $aiContext;
    private AiController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the ai_usage table so logUsage() doesn't fail
        Schema::create('ai_usage', function ($table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('admin_id')->nullable();
            $table->string('feature', 50)->nullable();
            $table->date('date')->nullable();
            $table->unsignedInteger('total_tokens')->default(0);
            $table->unsignedInteger('tokens_used')->nullable();
            $table->decimal('total_cost', 10, 4)->default(0);
            $table->decimal('cost', 10, 6)->nullable();
            $table->unsignedInteger('requests_count')->default(0);
            $table->decimal('response_time_ms', 10, 3)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->aiGateway = Mockery::mock(AiGateway::class);
        $this->aiContext = Mockery::mock(AiContextService::class);
        $this->controller = new AiController($this->aiGateway, $this->aiContext);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('ai_usage');
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function insights_returns_data_from_gateway(): void
    {
        $this->aiGateway->shouldReceive('generateInsights')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['content' => 'System is operating normally.'],
                'model' => 'test-model',
                'tokens_used' => 10,
                'processing_time' => 100.0,
                'cost' => 0.01,
            ]);

        $response = $this->controller->insights();

        $this->assertNotNull($response);
        $data = $response->getData(true);

        $this->assertArrayHasKey('data', $data);
        $this->assertTrue($data['data']['success']);
    }

    /** @test */
    public function chatbot_returns_reply_from_gateway(): void
    {
        $this->aiContext->shouldReceive('chatMessages')
            ->once()
            ->with(Mockery::any(), 'Hello', [])
            ->andReturn([
                ['role' => 'system', 'content' => 'Context message'],
                ['role' => 'user', 'content' => 'Hello'],
            ]);

        $this->aiGateway->shouldReceive('chatWithMessages')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn([
                'success' => true,
                'data' => ['content' => 'Hello! How can I help you with your application today?'],
                'model' => 'test-model',
                'tokens_used' => 15,
                'processing_time' => 50.0,
                'cost' => 0.001,
            ]);

        $request = Request::create('/api/v1/ai/chatbot', 'POST', [
            'message' => 'Hello',
        ]);

        $response = $this->controller->chatbot($request);
        $data = $response->getData(true);

        $this->assertArrayHasKey('data', $data);
        $this->assertStringContainsStringIgnoringCase('Hello', $data['data']['reply']);
    }

    /** @test */
    public function usage_returns_data_when_admin_authenticated(): void
    {
        // Seed some usage records
        AiUsage::create([
            'admin_id' => 1,
            'feature' => 'chatbot',
            'tokens_used' => 100,
            'cost' => 0.01,
            'requests_count' => 1,
        ]);

        AiUsage::create([
            'admin_id' => 1,
            'feature' => 'document_verification',
            'tokens_used' => 500,
            'cost' => 0.05,
            'requests_count' => 1,
        ]);

        $user = Mockery::mock(\Illuminate\Foundation\Auth\User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);

        $request = Request::create('/api/v1/ai/usage', 'GET');
        $request->setUserResolver(fn() => $user);

        $response = $this->controller->usage($request);
        $data = $response->getData(true);

        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']['usage']);
        $this->assertEquals(2, $data['data']['totals']['total_requests']);
    }
}
