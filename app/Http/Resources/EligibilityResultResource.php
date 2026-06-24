<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EligibilityResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'overall_status' => $this->overall_status,
            'age_check' => $this->age_check,
            'nationality_check' => $this->nationality_check,
            'education_check' => $this->education_check,
            'height_check' => $this->height_check,
            'criminal_check' => $this->criminal_check,
            'document_check' => $this->document_check,
            'marital_check' => $this->marital_check,
            'rejection_reasons' => $this->rejection_reasons,
            'ai_confidence' => $this->ai_confidence,
            'ai_explanation' => $this->ai_explanation,
            'evaluation_date' => $this->evaluation_date,
        ];
    }
}
