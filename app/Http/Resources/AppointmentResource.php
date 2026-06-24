<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scheduled_date' => $this->scheduled_date,
            'scheduled_time' => $this->scheduled_time,
            'venue' => $this->venue,
            'status' => $this->status,
            'notification_sent' => $this->notification_sent,
            'checked_in_at' => $this->checked_in_at,
        ];
    }
}
