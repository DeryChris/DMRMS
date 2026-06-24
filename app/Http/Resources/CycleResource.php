<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CycleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cycle_code' => $this->cycle_code,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'application_deadline' => $this->application_deadline,
            'total_vacancies' => $this->total_vacancies,
            'requirements' => $this->requirements,
            'ai_enabled' => $this->ai_enabled,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'applications_count' => $this->whenLoaded('applications', $this->applications_count ?? $this->applications->count()),
            'vouchers_count' => $this->whenLoaded('vouchers', $this->vouchers_count ?? $this->vouchers->count()),
            'created_at' => $this->created_at,
        ];
    }
}
