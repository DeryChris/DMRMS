<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'subject' => $this->subject,
            'message' => $this->message,
            'channel' => $this->channel,
            'sent_at' => $this->sent_at,
            'read_at' => $this->read_at,
            'is_read' => !is_null($this->read_at),
        ];
    }
}
