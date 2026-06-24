<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreeningResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medical_result' => $this->medical_result,
            'medical_notes' => $this->medical_notes,
            'fitness_result' => $this->fitness_result,
            'fitness_score' => $this->fitness_score,
            'interview_result' => $this->interview_result,
            'interview_notes' => $this->interview_notes,
            'overall_status' => $this->overall_status,
            'conducted_at' => $this->conducted_at,
        ];
    }
}
