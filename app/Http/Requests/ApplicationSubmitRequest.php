<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'education_level' => ['required', 'string'],
            'institution_name' => ['required', 'string'],
            'qualification' => ['required', 'string'],
            'year_obtained' => ['required', 'digits:4', 'integer', 'between:1950,' . date('Y')],
            'height' => ['required', 'numeric', 'between:1.00,2.50'],
            'criminal_record' => ['required', 'boolean', 'accepted:false'],
            'fitness_status' => ['nullable', 'string'],
            'health_conditions' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'year_obtained.between' => 'Year obtained must be between 1950 and ' . date('Y') . '.',
            'height.between' => 'Height must be between 1.00m and 2.50m as per GAF requirements.',
            'criminal_record.accepted' => 'You must declare no criminal record to proceed with GAF enlistment.',
            'criminal_record.required' => 'Please declare your criminal record status as required by GAF regulations.',
        ];
    }
}
