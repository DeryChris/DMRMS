<?php

namespace App\Console\Commands;

use App\Services\Ai\AiGateway;
use App\Services\Ai\Providers\FallbackProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestAiService extends Command
{
    protected $signature = 'ai:test
        {--provider=active : Provider to test: "active" (default), "openai", "fallback"}
        {--skip-db : Skip database table existence checks}
        {--show-content : Show full response content data}';

    protected $description = 'Run smoke tests against the AI service to verify it is functioning correctly';

    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;

    public function handle(): int
    {
        $this->info('🧪 DMRMS AI Service Test Suite');
        $this->newLine();

        // ── Phase 1: Database table verification ──
        if (!$this->option('skip-db')) {
            $this->phaseDatabaseTables();
        } else {
            $this->warn('⏭ Skipping database table checks (--skip-db)');
        }

        // ── Phase 2: Service container resolution ──
        $this->phaseServiceResolution();

        // ── Phase 3: Provider tests ──
        $providerName = $this->option('provider');

        match ($providerName) {
            'openai'   => $this->testProvider('openai', fn() => $this->testOpenAiProvider()),
            'fallback' => $this->testProvider('fallback', fn() => $this->testFallbackProvider()),
            default    => $this->testAllProviders(),
        };

        // ── Summary ──
        $this->newLine();
        $this->outputSummary();

        return $this->failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    // ──────────────────────────────────────────────
    //  Phase 1: Database table checks
    // ──────────────────────────────────────────────

    private function phaseDatabaseTables(): void
    {
        $this->section('Database Table Verification');

        $tables = ['ai_usage', 'documents'];
        $connection = config('database.default');

        $this->line("  Connection: <comment>{$connection}</comment>");

        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            if ($exists) {
                $this->recordPass("Table <comment>{$table}</comment> exists");
            } else {
                $this->recordFail("Table <comment>{$table}</comment> is MISSING");
            }
        }

        // Check we can query ai_usage
        if (Schema::hasTable('ai_usage')) {
            try {
                $count = DB::table('ai_usage')->count();
                $this->line("    → ai_usage has <comment>{$count}</comment> record(s)");
            } catch (\Exception $e) {
                $this->recordFail("Cannot query ai_usage: {$e->getMessage()}");
            }
        }

        if (Schema::hasTable('documents')) {
            try {
                $columns = Schema::getColumnListing('documents');
                $this->line("    → documents has columns: <comment>" . implode(', ', $columns) . "</comment>");
            } catch (\Exception $e) {
                $this->recordFail("Cannot describe documents: {$e->getMessage()}");
            }
        }

        $this->newLine();
    }

    // ──────────────────────────────────────────────
    //  Phase 2: Service container resolution
    // ──────────────────────────────────────────────

    private function phaseServiceResolution(): void
    {
        $this->section('Service Container Resolution');

        $services = [
            AiGateway::class        => 'AiGateway',
            FallbackProvider::class => 'FallbackProvider',
            OpenAiProvider::class   => 'OpenAiProvider',
        ];

        foreach ($services as $class => $label) {
            try {
                $instance = app($class);
                $this->recordPass("{$label} resolved from container");
            } catch (\Exception $e) {
                $this->recordFail("{$label} FAILED: {$e->getMessage()}");
            }
        }

        $this->newLine();
    }

    // ──────────────────────────────────────────────
    //  Phase 3: Provider tests
    // ──────────────────────────────────────────────

    private function testAllProviders(): void
    {
        $this->testProvider('openai', fn() => $this->testOpenAiProvider());
        $this->testProvider('fallback', fn() => $this->testFallbackProvider());
    }

    private function testProvider(string $name, callable $callback): void
    {
        $this->section("Provider: {$name}");
        $callback();
        $this->newLine();
    }

    private function testOpenAiProvider(): void
    {
        $apiKey = config('ai.openai.api_key');
        if (empty($apiKey)) {
            $this->warn('  ⚠ OPENAI_API_KEY is not configured — skipping live tests');
            $this->line('    Set AI_PROVIDER=fallback or configure OPENAI_API_KEY in .env');
            return;
        }

        /** @var OpenAiProvider $provider */
        $provider = app(OpenAiProvider::class);

        // 1. Chat / health check
        $this->testStep('Chat / Health Check', function () use ($provider) {
            $result = $provider->chat([['role' => 'user', 'content' => 'Say "pong" and nothing else.']]);
            $this->assertResult($result, 'chat');
            $this->dumpContent($result);
        });

        // 2. Embeddings
        $this->testStep('Embeddings', function () use ($provider) {
            $result = $provider->getEmbeddings('DMRMS test query');
            $this->assertResult($result, 'embeddings');
            if (isset($result['data']['embedding'])) {
                $this->recordPass("Embedding vector has " . count($result['data']['embedding']) . " dimensions");
            }
        });

        // 3. Fallback: analyzeDocument needs a real file, skip in smoke test
        $this->line('  ⏭ analyzeDocument — skipped (requires image file path)');
    }

    private function testFallbackProvider(): void
    {
        /** @var FallbackProvider $provider */
        $provider = app(FallbackProvider::class);

        // 1. Chat
        $this->testStep('Chat', function () use ($provider) {
            $result = $provider->chat([
                ['role' => 'user', 'content' => 'What are the eligibility requirements?'],
            ]);
            $this->assertResult($result, 'fallback-chat');
            $this->assertStringContains($result['data']['content'] ?? '', 'eligibility');
            $this->assertModel($result['model'], 'fallback-rule-based');
        });

        // 2. Analyze Document (no real file needed — fallback doesn't read it)
        $this->testStep('Analyze Document', function () use ($provider) {
            $result = $provider->analyzeDocument('/fake/path.jpg', 'national_id', [
                'reference_data' => [
                    'first_name' => 'John',
                    'last_name'  => 'Doe',
                    'date_of_birth' => '2000-01-15',
                    'nationality' => 'Ghanaian',
                ],
            ]);
            $this->assertResult($result, 'fallback-analyze');
            $this->assertStringContains($result['data']['overall']['verdict'] ?? '', 'needs_review');
            $this->assertModel($result['model'], 'fallback-ocr');
        });

        // 3. Cross Verify Documents
        $this->testStep('Cross Verify Documents', function () use ($provider) {
            $result = $provider->crossVerifyDocuments(
                [
                    ['label' => 'National ID', 'path' => '/fake/id.jpg'],
                    ['label' => 'Birth Certificate', 'path' => '/fake/birth.jpg'],
                ],
                ['first_name' => 'Jane', 'last_name' => 'Smith'],
                'Verify these documents are genuine.'
            );
            $this->assertResult($result, 'fallback-cross-verify');
            $this->assertStringContains($result['data']['overall_status'] ?? '', 'insufficient');
            $this->assertModel($result['model'], 'fallback-cross-verify');
        });

        // 4. Embeddings
        $this->testStep('Embeddings', function () use ($provider) {
            $result = $provider->getEmbeddings('Test text for embedding');
            $this->assertResult($result, 'fallback-embedding');
            $this->assertTrue(
                isset($result['data']['embedding']) && count($result['data']['embedding']) === 1536,
                'Embedding should be 1536 dimensions'
            );
            $this->assertModel($result['model'], 'fallback-embedding');
        });

        // 5. Generate Ranking
        $this->testStep('Generate Ranking', function () use ($provider) {
            $candidates = [
                ['name' => 'Alice', 'score' => 85, 'education' => 'bachelor'],
                ['name' => 'Bob', 'score' => 72, 'education' => 'diploma'],
                ['name' => 'Charlie', 'score' => 90, 'education' => 'master'],
            ];
            $result = $provider->generateRanking($candidates, ['minimum_score' => 70, 'minimum_education' => 'diploma']);
            $this->assertResult($result, 'fallback-ranking');
            $this->assertTrue(
                isset($result['data']['ranked_candidates']) && count($result['data']['ranked_candidates']) === 3,
                'Should rank all 3 candidates'
            );
            $this->assertModel($result['model'], 'fallback-ranking');
        });
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    private function testStep(string $label, callable $callback): void
    {
        $this->line("  Test: {$label}");
        try {
            $callback();
        } catch (\Exception $e) {
            $this->recordFail("Exception: {$e->getMessage()}");
        }
    }

    private function assertResult(array $result, string $context): void
    {
        if ($result['success'] ?? false) {
            $this->recordPass("success=true");
        } else {
            $this->recordFail("success=false — " . ($result['error'] ?? 'no error message'));
            return;
        }

        // Verify structure
        $this->assertArrayHasKey('model', $result, $context);
        $this->assertArrayHasKey('tokens_used', $result, $context);
        $this->assertArrayHasKey('processing_time', $result, $context);
        $this->assertArrayHasKey('cost', $result, $context);

        $this->line("    → model: <comment>{$result['model']}</comment>, "
            . "tokens: <comment>{$result['tokens_used']}</comment>, "
            . "time: <comment>{$result['processing_time']}ms</comment>, "
            . "cost: <comment>\${$result['cost']}</comment>");
    }

    private function assertStringContains(string $haystack, string $needle): void
    {
        if (str_contains(strtolower($haystack), strtolower($needle))) {
            $this->recordPass("Contains '{$needle}'");
        } else {
            $this->recordFail("Expected to contain '{$needle}'");
        }
    }

    private function assertModel(string $model, string $expected): void
    {
        if ($model === $expected) {
            $this->recordPass("Model: {$model}");
        } else {
            $this->recordFail("Expected model '{$expected}', got '{$model}'");
        }
    }

    private function assertArrayHasKey(string $key, array $array, string $context): void
    {
        if (array_key_exists($key, $array)) {
            $this->recordPass("Has key '{$key}'");
        } else {
            $this->recordFail("Missing key '{$key}' in {$context}");
        }
    }

    private function assertTrue(bool $condition, string $message): void
    {
        if ($condition) {
            $this->recordPass($message);
        } else {
            $this->recordFail($message);
        }
    }

    private function dumpContent(array $result): void
    {
        if ($this->option('show-content') && isset($result['data']['content'])) {
            $this->line('    ── Content ──');
            $this->line('    ' . substr($result['data']['content'], 0, 500));
            $this->line('    ─────────────');
        }
    }

    private function recordPass(string $detail): void
    {
        $this->passed++;
        $this->line("    ✅ {$detail}");
    }

    private function recordFail(string $detail): void
    {
        $this->failed++;
        $this->line("    ❌ {$detail}");
    }

    private function section(string $title): void
    {
        $this->line('');
        $this->line('━━━ ' . $title . ' ━━━');
    }

    private function outputSummary(): void
    {
        $total = $this->passed + $this->failed;
        $this->line(str_repeat('━', 50));
        $this->line("  Results: <comment>{$this->passed} passed</comment>, "
            . "<fg=red>{$this->failed} failed</fg=red>, "
            . "<comment>{$total} total</comment>");

        if ($this->failed === 0) {
            $this->info('  ✅ All AI service tests passed!');
        } else {
            $this->error("  ❌ {$this->failed} test(s) failed — check output above.");
        }
    }
}
