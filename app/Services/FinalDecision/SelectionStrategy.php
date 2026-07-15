<?php

namespace App\Services\FinalDecision;

use App\Models\Application;
use Illuminate\Support\Collection;

interface SelectionStrategy
{
    public function select(Collection $applications, int $availablePositions): SelectionResult;
}
