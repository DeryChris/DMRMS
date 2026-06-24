<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gaf_id' => $this->gaf_id,
            'applicant' => ApplicantResource::make($this->whenLoaded('applicant')),
            'cycle' => CycleResource::make($this->whenLoaded('cycle')),
            'education_level' => $this->education_level,
            'institution_name' => $this->institution_name,
            'qualification' => $this->qualification,
            'year_obtained' => $this->year_obtained,
            'height' => $this->height,
            'weight' => $this->weight,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'eligibility' => EligibilityResultResource::make($this->whenLoaded('eligibilityResult')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'appointment' => AppointmentResource::make($this->whenLoaded('appointment')),
            'screening' => ScreeningResultResource::make($this->whenLoaded('screeningResult')),
            'decision' => FinalDecisionResource::make($this->whenLoaded('finalDecision')),
        ];
    }
}
