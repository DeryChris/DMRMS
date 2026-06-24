<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after:today'],
            'time' => ['required', 'date_format:H:i'],
            'venue' => ['required', 'string', 'max:200'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }
}
