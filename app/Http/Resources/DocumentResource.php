<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'file_name' => $this->file_name,
            'file_url' => $this->file_url,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'verification_status' => $this->verification_status,
            'uploaded_at' => $this->upload_date ?? $this->created_at,
        ];
    }
}
