<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Models\Cycle;
use App\Models\AiUsage;
use App\Models\Subscription;
use App\Services\Ai\AiGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    protected AiGatewayService $aiGateway;

    public function __construct(AiGatewayService $aiGateway)
    {
        $this->aiGateway = $aiGateway;
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

        $document->update([
            'verification_status' => $result['verified'] ?? false ? 'verified' : 'rejected',
            'ai_confidence'       => $result['confidence'] ?? null,
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

        $messages = array_merge(
            [['role' => 'system', 'content' => 'You are an AI assistant for the Defence Manpower Recruitment Management System (DMRMS).']],
            $history,
            [['role' => 'user', 'content' => $validated['message']]]
        );

        $response = $this->aiGateway->chat($messages);

        $this->logUsage($request, 'chatbot', $response);

        return response()->json([
            'data' => [
                'reply'   => $response['content'] ?? 'I am unable to process your request at this time.',
                'tokens'  => $response['tokens'] ?? null,
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

        $usage = AiUsage::where('user_id', $admin->id)
            ->selectRaw('feature, COUNT(*) as count, SUM(tokens_used) as total_tokens, SUM(cost) as total_cost')
            ->groupBy('feature')
            ->get();

        $totals = AiUsage::where('user_id', $admin->id)
            ->selectRaw('COUNT(*) as total_requests, SUM(tokens_used) as total_tokens, SUM(cost) as total_cost')
            ->first();

        $subscription = Subscription::where('user_id', $admin->id)->first();

        return response()->json([
            'data' => [
                'usage'        => $usage,
                'totals'       => $totals,
                'subscription' => $subscription,
            ],
        ]);
    }

    private function logUsage(Request $request, string $feature, array $result): void
    {
        AiUsage::create([
            'user_id'         => $request->user()?->id,
            'feature'         => $feature,
            'tokens_used'     => $result['tokens'] ?? $result['tokens_used'] ?? 0,
            'cost'            => $result['cost'] ?? 0,
            'response_time_ms'=> $result['response_time_ms'] ?? null,
            'metadata'        => json_encode($result),
        ]);
    }
}
