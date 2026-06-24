<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScreeningResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => ['required', 'exists:applications,id'],
            'medical_result' => ['sometimes', 'in:pass,fail,pending'],
            'medical_notes' => ['nullable', 'string'],
            'fitness_result' => ['sometimes', 'in:pass,fail,pending'],
            'fitness_score' => ['nullable', 'integer', 'between:0,100'],
            'interview_result' => ['sometimes', 'in:pass,fail,pending'],
            'interview_notes' => ['nullable', 'string'],
        ];
    }
}
