<?php

namespace App\Services\FinalDecision;

use App\Models\Application;
use Illuminate\Support\Collection;

class DecisionScoringService
{
    private array $weights;

    public function __construct(?array $weights = null, ?array $cycleWeights = null)
    {
        $this->weights = $cycleWeights ?? $weights ?? config('recruitment.scoring_weights', [
            'medical'    => 0.40,
            'interview'  => 0.30,
            'fitness'    => 0.20,
            'eligibility' => 0.10,
        ]);
    }

    public function score(Application $application): array
    {
        $screening = $application->screeningResult;
        $eligibility = $application->eligibilityResult;

        $medicalScore = $this->scoreMedical($screening?->overall_status ?? 'pending');
        $interviewScore = $this->scoreInterview($screening?->interview_grade ?? $screening?->interview_data['score'] ?? null);
        $fitnessScore = $this->scoreFitness($screening?->fitness_grade ?? $screening?->fitness_data['fitness_grade'] ?? null);
        $eligibilityScore = $eligibility?->overall_status === 'eligible' ? 100 : 0;

        $composite = round(
            $medicalScore * $this->weights['medical'] +
            $interviewScore * $this->weights['interview'] +
            $fitnessScore * $this->weights['fitness'] +
            $eligibilityScore * $this->weights['eligibility'],
            2
        );

        return [
            'application_id' => $application->id,
            'applicant_name' => $application->applicant?->name,
            'gaf_id' => $application->gaf_id,
            'scores' => [
                'medical' => $medicalScore,
                'interview' => $interviewScore,
                'fitness' => $fitnessScore,
                'eligibility' => $eligibilityScore,
            ],
            'weights' => $this->weights,
            'composite' => $composite,
        ];
    }

    public function scoreBatch(Collection $applications): Collection
    {
        return $applications->map(fn(Application $app) => $this->score($app))
            ->sortByDesc('composite')
            ->values();
    }

    private function scoreMedical(?string $status): float
    {
        return match ($status) {
            'fit' => 100,
            'unfit' => 0,
            'pending' => 50,
            default => 0,
        };
    }

    private function scoreInterview(mixed $score): float
    {
        if ($score === null || $score === '') return 0;
        if (is_numeric($score)) return min((float)$score / 10 * 100, 100);
        return match (strtolower((string)$score)) {
            'pass', 'recommended', 'excellent' => 100,
            'average', 'fair' => 60,
            'fail', 'poor', 'not_recommended' => 0,
            default => 50,
        };
    }

    private function scoreFitness(mixed $grade): float
    {
        if ($grade === null || $grade === '') return 0;
        if (is_numeric($grade)) return min((float)$grade, 100);
        return match (strtolower((string)$grade)) {
            'a', 'a+', 'excellent', 'pass' => 100,
            'b', 'b+', 'good' => 80,
            'c', 'average', 'fair' => 60,
            'd', 'poor', 'fail' => 0,
            default => 50,
        };
    }
}
