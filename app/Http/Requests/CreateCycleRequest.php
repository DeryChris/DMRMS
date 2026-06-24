<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'cycle_code' => ['required', 'string', 'max:20', 'unique:cycles,cycle_code'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'application_deadline' => ['required', 'date', 'before_or_equal:end_date'],
            'total_vacancies' => ['required', 'integer', 'min:1'],
            'requirements' => ['nullable', 'array'],
            'ai_enabled' => ['boolean'],
        ];
    }
}
