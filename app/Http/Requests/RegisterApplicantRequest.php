<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:18 years ago'],
            'gender' => ['required', 'in:Male,Female'],
            'contact_number' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'unique:applicants,email'],
            'residential_address' => ['required', 'string'],
            'region' => ['required', 'string', 'max:50'],
            'district' => ['required', 'string', 'max:50'],
            'national_id' => ['required', 'string', 'max:20'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'You must be at least 18 years old to register.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'email.unique' => 'This email address is already registered.',
            'gender.in' => 'Gender must be either Male or Female.',
        ];
    }
}
