<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'in:birth_certificate,educational_cert,national_id,passport_photo,wassce_cert,degree_cert'],
            'file' => ['required', 'file', 'mimes:jpeg,png,pdf', 'max:2048'],
        ];
    }
}
