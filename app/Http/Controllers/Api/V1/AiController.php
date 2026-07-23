<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Models\Cycle;
use App\Models\AiUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Ai\AiGateway;
use App\Services\AiContextService;

class AiController extends Controller
{
    protected AiGateway $aiGateway;
    protected AiContextService $aiContext;

    public function __construct(AiGateway $aiGateway, AiContextService $aiContext)
    {
        $this->aiGateway = $aiGateway;
        $this->aiContext = $aiContext;
    }

    public function eligibilityAnalysis(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
        ]);

        $application = Application::with(['applicant', 'cycle'])->findOrFail($validated['application_id']);

        $result = $this->aiGateway->analyzeEligibility($application);

        $this->logUsage($request, 'eligibility_analysis', $result);

        return response()->json([
            'message' => 'Eligibility analysis complete.',
            'data'    => $result,
        ]);
    }

    public function documentVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:documents,id',
        ]);

        $document = Document::with('applicant')->findOrFail($validated['document_id']);

        $result = $this->aiGateway->analyzeDocument(
            storage_path("app/public/{$document->file_path}"),
            $document->document_type
        );

        $content = $result['data']['content'] ?? '{}';
        $parsed = $this->parseJsonContent($content);
        $verdict = $parsed['overall']['verdict'] ?? 'needs_review';
        $confidence = $parsed['overall']['confidence'] ?? null;

        $document->update([
            'verification_status' => match ($verdict) {
                'verified' => 'verified',
                'rejected' => 'rejected',
                default => 'pending',
            },
            'ai_confidence'       => $confidence,
            'ai_analysis'         => json_encode($result),
        ]);

        $this->logUsage($request, 'document_verification', $result);

        return response()->json([
            'message'  => 'Document verification complete.',
            'data'     => $result,
        ]);
    }

    public function rankingList(Request $request): JsonResponse
    {
        $cycleId = $request->input('cycle_id');
        $cycle = $cycleId ? Cycle::findOrFail($cycleId) : Cycle::where('status', 'active')->firstOrFail();

        $applications = Application::where('cycle_id', $cycle->id)
            ->where('status', 'qualified')
            ->with('applicant')
            ->get();

        $candidates = $applications->map(fn($app) => [
            'id'          => $app->applicant_id,
            'name'        => "{$app->applicant->first_name} {$app->applicant->last_name}",
            'score'       => $app->eligibility?->score ?? 0,
            'education'   => $app->highest_education,
            'region'      => $app->applicant->region,
        ])->toArray();

        $requirements = $cycle->requirements ?? [];

        $ranking = $this->aiGateway->generateRanking($candidates, $requirements);

        $this->logUsage($request, 'ranking', $ranking);

        return response()->json([
            'data' => [
                'cycle'       => $cycle->name,
                'rankings'    => $ranking['rankings'] ?? $ranking,
                'generated_at'=> now(),
            ],
        ]);
    }

    public function chatbot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $history = $request->input('history', []);

        $messages = $this->aiContext->chatMessages(applicant: null, message: $validated['message'], history: $history);

        $response = $this->aiGateway->chatWithMessages($messages);

        $this->logUsage($request, 'chatbot', $response);

        return response()->json([
            'data' => [
                'reply'   => $response['data']['content'] ?? 'I am unable to process your request at this time.',
                'tokens'  => $response['tokens_used'] ?? null,
            ],
        ]);
    }

    public function insights(): JsonResponse
    {
        $insights = $this->aiGateway->generateInsights();

        $this->logUsage(request(), 'insights', $insights);

        return response()->json([
            'data' => $insights,
        ]);
    }

    public function reportGeneration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'    => 'required|in:summary,detailed,statistical',
            'cycle_id'=> 'nullable|exists:cycles,id',
            'format'  => 'nullable|in:paragraph,bullet_points',
        ]);

        $cycleId = $validated['cycle_id'] ?? null;
        $cycle = $cycleId ? Cycle::find($cycleId) : Cycle::where('status', 'active')->first();

        $applications = Application::where('cycle_id', $cycle->id ?? null)
            ->with('applicant')
            ->get();

        $report = $this->aiGateway->generateReport([
            'type'    => $validated['type'],
            'format'  => $validated['format'] ?? 'paragraph',
            'cycle'   => $cycle?->name ?? 'All Cycles',
            'data'    => [
                'total_applicants'  => $applications->count(),
                'by_status'         => $applications->groupBy('status')->map->count(),
                'by_region'         => $applications->groupBy('applicant.region')->map->count(),
                'by_gender'         => $applications->groupBy('applicant.gender')->map->count(),
            ],
        ]);

        $this->logUsage($request, 'report_generation', $report);

        return response()->json([
            'message' => 'Report generated.',
            'data'    => $report,
        ]);
    }

    public function usage(Request $request): JsonResponse
    {
        $admin = $request->user();

        $usage = AiUsage::where('admin_id', $admin->id)
            ->selectRaw('feature, COUNT(*) as count, SUM(tokens_used) as total_tokens, SUM(cost) as total_cost')
            ->groupBy('feature')
            ->get();

        $totals = AiUsage::where('admin_id', $admin->id)
            ->selectRaw('COUNT(*) as total_requests, SUM(tokens_used) as total_tokens, SUM(cost) as total_cost')
            ->first();

        return response()->json([
            'data' => [
                'usage'        => $usage,
                'totals'       => $totals,
            ],
        ]);
    }

    private function parseJsonContent(string $text): array
    {
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);
        if (preg_match('/\{.*\}/s', $text, $match)) {
            $decoded = json_decode($match[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return [];
    }

    private function logUsage(Request $request, string $feature, array $result): void
    {
        AiUsage::create([
            'admin_id'         => $request->user()?->id,
            'feature'         => $feature,
            'tokens_used'     => $result['tokens_used'] ?? $result['tokens'] ?? 0,
            'cost'            => $result['cost'] ?? 0,
            'response_time_ms'=> $result['processing_time'] ?? $result['response_time_ms'] ?? null,
            'metadata'        => json_encode($result),
        ]);
    }
}
