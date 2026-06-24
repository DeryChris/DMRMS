<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $table = 'ai_prompts';

    protected $fillable = [
        'name',
        'prompt_text',
        'model',
        'parameters',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
