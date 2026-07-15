<?php

namespace App\Services\FinalDecision;

use App\Models\Application;
use Illuminate\Support\Collection;

class DefaultSelectionStrategy implements SelectionStrategy
{
    public function __construct(
        private readonly DecisionScoringService $scorer,
        private readonly float $reserveRatio = 0.2,
    ) {}

    public function select(Collection $applications, int $availablePositions): SelectionResult
    {
        $ranked = $this->scorer->scoreBatch($applications);
        $reserveCount = (int)ceil($availablePositions * $this->reserveRatio);

        $selected = $ranked->take($availablePositions)->values();
        $reserve = $ranked->slice($availablePositions, $reserveCount)->values();
        $rejected = $ranked->slice($availablePositions + $reserveCount)->values();

        return new SelectionResult(
            selected: $selected->toArray(),
            reserve: $reserve->toArray(),
            rejected: $rejected->toArray(),
            ranked: $ranked->toArray(),
        );
    }
}
