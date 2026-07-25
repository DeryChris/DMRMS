<?php

namespace Tests\Unit\Ai;

use App\Services\Ai\Providers\FallbackProvider;
use PHPUnit\Framework\TestCase;

class FallbackProviderTest extends TestCase
{
    private FallbackProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new FallbackProvider();
    }

    /** @test */
    public function it_returns_structured_chat_response_with_eligibility_keyword(): void
    {
        $result = $this->provider->chat([
            ['role' => 'user', 'content' => 'What are the eligibility requirements for enlistment?'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('content', $result['data']);
        $this->assertStringContainsStringIgnoringCase('eligibility', $result['data']['content']);
        $this->assertSame('fallback-rule-based', $result['model']);
        $this->assertSame(0, $result['tokens_used']);
        $this->assertSame(0.0, $result['cost']);
        $this->assertGreaterThanOrEqual(0.0, $result['processing_time']);
    }

    /** @test */
    public function it_returns_chat_response_with_ranking_keyword(): void
    {
        $result = $this->provider->chat([
            ['role' => 'user', 'content' => 'Rank the candidates by their scores.'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsStringIgnoringCase('candidates', $result['data']['content']);
        $this->assertStringContainsStringIgnoringCase('evaluated', $result['data']['content']);
    }

    /** @test */
    public function it_returns_chat_response_with_document_keyword(): void
    {
        $result = $this->provider->chat([
            ['role' => 'user', 'content' => 'Verify this document for me.'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsStringIgnoringCase('document', $result['data']['content']);
    }

    /** @test */
    public function it_returns_generic_fallback_when_no_keywords_match(): void
    {
        $result = $this->provider->chat([
            ['role' => 'user', 'content' => 'Hello, how are you today?'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsStringIgnoringCase('fallback response', $result['data']['content']);
    }

    /** @test */
    public function it_returns_needs_review_for_document_analysis(): void
    {
        $result = $this->provider->analyzeDocument('/tmp/test.jpg', 'national_id', [
            'reference_data' => [
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'date_of_birth'=> '2000-01-15',
                'nationality'  => 'Ghanaian',
                'gender'       => 'Male',
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('needs_review', $result['data']['overall']['verdict']);
        $this->assertSame(0.0, $result['data']['overall']['confidence']);
        $this->assertSame('fallback-ocr', $result['model']);
        $this->assertCount(1, $result['data']['overall']['reasons']);
        $this->assertStringContainsStringIgnoringCase('Fallback mode', $result['data']['overall']['reasons'][0]);

        // Verify extracted fields from reference data
        $this->assertSame('John Doe', $result['data']['extracted_fields']['full_name']);
        $this->assertSame('2000-01-15', $result['data']['extracted_fields']['date_of_birth']);
        $this->assertSame('Ghanaian', $result['data']['extracted_fields']['nationality']);
        $this->assertSame('Male', $result['data']['extracted_fields']['gender']);
    }

    /** @test */
    public function it_generates_deterministic_document_number_from_file_path(): void
    {
        $context = ['reference_data' => [
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]];

        $result1 = $this->provider->analyzeDocument('/path/to/doc1.jpg', 'birth_certificate', $context);
        $result2 = $this->provider->analyzeDocument('/path/to/doc1.jpg', 'birth_certificate', $context);
        $result3 = $this->provider->analyzeDocument('/different/path.jpg', 'birth_certificate', $context);

        $this->assertSame(
            $result1['data']['extracted_fields']['document_number'],
            $result2['data']['extracted_fields']['document_number']
        );
        $this->assertNotSame(
            $result1['data']['extracted_fields']['document_number'],
            $result3['data']['extracted_fields']['document_number']
        );
    }

    /** @test */
    public function it_returns_insufficient_for_cross_verify(): void
    {
        $result = $this->provider->crossVerifyDocuments(
            [
                ['label' => 'National ID', 'path' => '/fake/id.jpg'],
                ['label' => 'Birth Certificate', 'path' => '/fake/birth.jpg'],
            ],
            ['first_name' => 'Jane'],
            'Please verify identity.'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('insufficient', $result['data']['overall_status']);
        $this->assertSame(0.0, $result['data']['overall_confidence']);
        $this->assertSame('fallback-cross-verify', $result['model']);
        $this->assertArrayHasKey('comparison_summary', $result['data']);
        $this->assertArrayHasKey('detected_inconsistencies', $result['data']);
    }

    /** @test */
    public function it_returns_1536_dimensional_embedding_vector(): void
    {
        $result = $this->provider->getEmbeddings('Test text for DMRMS');

        $this->assertTrue($result['success']);
        $this->assertCount(1536, $result['data']['embedding']);
        $this->assertSame(1536, $result['data']['dimension']);
        $this->assertSame('fallback-embedding', $result['model']);
        $this->assertSame(0, $result['tokens_used']);

        // All values should be floats between 0 and 1
        foreach ($result['data']['embedding'] as $value) {
            $this->assertIsFloat($value);
            $this->assertGreaterThanOrEqual(0.0, $value);
            $this->assertLessThanOrEqual(1.0, $value);
        }
    }

    /** @test */
    public function it_returns_deterministic_embeddings_for_same_text(): void
    {
        $text = 'Consistent input text for testing';

        $result1 = $this->provider->getEmbeddings($text);
        $result2 = $this->provider->getEmbeddings($text);

        $this->assertSame($result1['data']['embedding'], $result2['data']['embedding']);
    }

    /** @test */
    public function it_returns_ranked_candidates_in_descending_order(): void
    {
        $candidates = [
            ['name' => 'Alice', 'score' => 85, 'education' => 'bachelor'],
            ['name' => 'Bob', 'score' => 72, 'education' => 'diploma'],
            ['name' => 'Charlie', 'score' => 90, 'education' => 'master'],
        ];

        $result = $this->provider->generateRanking($candidates, [
            'minimum_score' => 70,
            'minimum_education' => 'diploma',
        ]);

        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']['ranked_candidates']);
        $this->assertSame('fallback-ranking', $result['model']);

        // Verify descending order
        $scores = array_map(fn($item) => $item['score'], $result['data']['ranked_candidates']);
        $sorted = $scores;
        rsort($sorted);
        $this->assertSame($sorted, $scores, 'Candidates must be sorted by score descending');

        // Each candidate should have the original data, score, and explanation
        foreach ($result['data']['ranked_candidates'] as $item) {
            $this->assertArrayHasKey('candidate', $item);
            $this->assertArrayHasKey('score', $item);
            $this->assertArrayHasKey('explanation', $item);
            $this->assertGreaterThanOrEqual(50.0, $item['score']);
            $this->assertLessThanOrEqual(100.0, $item['score']);
        }
    }

    /** @test */
    public function it_returns_rankings_with_higher_score_for_better_matches(): void
    {
        $candidates = [
            ['name' => 'Perfect Match', 'education' => 'master', 'skills' => 'advanced'],
            ['name' => 'Weak Match', 'education' => 'high school', 'skills' => 'basic'],
        ];

        $result = $this->provider->generateRanking($candidates, [
            'education' => 'master',
            'skills' => 'advanced',
        ]);

        // Perfect match should score higher
        $this->assertGreaterThan(
            $result['data']['ranked_candidates'][1]['score'],
            $result['data']['ranked_candidates'][0]['score']
        );
    }

    /** @test */
    public function it_handles_empty_candidates_list(): void
    {
        $result = $this->provider->generateRanking([], []);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['data']['ranked_candidates']);
    }

    /** @test */
    public function it_handles_empty_messages_in_chat(): void
    {
        $result = $this->provider->chat([]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsStringIgnoringCase('fallback response', $result['data']['content']);
    }
}
