<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isOwner = $request->user()?->id === $this->id;
        $isAdmin = $request->user()?->role === 'admin' || $request->user()?->role === 'super_admin';

        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'other_names' => $this->other_names,
            'full_name' => $this->full_name ?? trim("{$this->first_name} {$this->other_names} {$this->last_name}"),
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->date_of_birth?->age,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'contact_number' => $this->contact_number,
            'email' => $this->email,
            'region' => $this->region,
            'district' => $this->district,
            'nationality' => $this->nationality,
            'national_id' => $this->national_id,
            'status' => $this->status,
        ];

        if (!$isOwner && !$isAdmin) {
            unset($data['contact_number'], $data['email'], $data['national_id'], $data['date_of_birth']);
        }

        return $data;
    }
}
