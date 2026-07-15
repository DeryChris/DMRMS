<?php

namespace App\Services\FinalDecision;

class SelectionResult
{
    public function __construct(
        public readonly array $selected,
        public readonly array $reserve,
        public readonly array $rejected,
        public readonly array $ranked,
    ) {}
}
