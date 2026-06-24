<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinalDecisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'decision' => $this->decision,
            'decision_reason' => $this->decision_reason,
            'decision_date' => $this->decision_date,
            'notification_sent' => $this->notification_sent,
        ];
    }
}
