<?php

namespace App\Jobs;

use App\Jobs\AutoRecruit;
use App\Models\Application;
use App\Models\Barrack;
use App\Models\FinalDecision;
use App\Models\ReserveList;
use App\Services\FinalDecision\DecisionScoringService;
use App\Services\FinalDecision\DefaultSelectionStrategy;
use App\Services\FinalDecision\SelectionResult;
use App\Services\FinalDecision\SelectionStrategy;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessFinalDecision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 120;

    public function handle(
        NotificationService $notifier,
        ?SelectionStrategy $strategy = null,
    ): void {
        try {
            $applications = Application::with(['applicant', 'screeningResult', 'eligibilityResult'])
                ->where('status', 'screening_completed')
                ->whereDoesntHave('finalDecision')
                ->get();

            if ($applications->isEmpty()) {
                Log::info('ProcessFinalDecision: No screened applicants awaiting decision');
                return;
            }

            $cycle = $applications->first()->cycle;
            $availablePositions = $cycle?->total_vacancies ?? 100;

            $scorer = new DecisionScoringService(
                weights: config('recruitment.scoring_weights'),
                cycleWeights: $cycle?->scoring_weights,
            );

            $reserveRatio = $cycle?->scoring_weights['reserve_ratio'] ?? config('recruitment.reserve_ratio', 0.2);

            $strategy ??= new DefaultSelectionStrategy($scorer, (float) $reserveRatio);

            $result = $strategy->select($applications, $availablePositions);

            DB::transaction(function () use ($result, $notifier) {
                $this->applySelection('selected', $result->selected, $notifier);
                $this->applySelection('reserve', $result->reserve, $notifier, true);
                $this->applySelection('rejected', $result->rejected, $notifier);
            });

            Log::info('ProcessFinalDecision completed', [
                'selected' => count($result->selected),
                'reserve' => count($result->reserve),
                'rejected' => count($result->rejected),
                'total' => $applications->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessFinalDecision job failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function applySelection(
        string $status,
        array $items,
        NotificationService $notifier,
        bool $isReserve = false,
    ): void {
        foreach ($items as $item) {
            $application = Application::find($item['application_id']);
            if (!$application) continue;

            $application->update(['status' => $status]);

            $decisionValue = match ($status) {
                'selected' => 'selected',
                'reserve' => 'reserve',
                default => 'rejected',
            };

            $decisionData = [
                'application_id' => $application->id,
                'decision' => $decisionValue,
                'decision_date' => Carbon::now(),
                'decided_by' => null,
                'notes' => match ($status) {
                    'selected' => 'Auto-selected by AI scoring',
                    'reserve' => 'Auto-assigned to reserve list',
                    default => 'Auto-rejected by AI scoring',
                },
            ];

            if ($status === 'selected') {
                $decisionData['reporting_code'] = 'GAF-' . strtoupper(substr(uniqid(), -8));

                $applicant = $application->applicant;
                if ($applicant?->region) {
                    $barrack = Barrack::where('region', $applicant->region)->where('is_active', true)->first();
                    if ($barrack) {
                        $decisionData['barrack_id'] = $barrack->id;
                    }
                }
            }

            FinalDecision::create($decisionData);

            if ($isReserve) {
                ReserveList::create([
                    'application_id' => $application->id,
                    'priority_score' => $item['composite'] ?? 0,
                    'cycle_id' => $application->cycle_id,
                ]);
            }

            $notifier->finalDecision($application);

            if ($status === 'selected' && config('recruitment.auto_recruit.enabled', false)) {
                $delayDays = (int) config('recruitment.auto_recruit.enrollment_delay_days', 14);
                AutoRecruit::dispatch($application)->delay(now()->addDays($delayDays));
            }
        }
    }
}
