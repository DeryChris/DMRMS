<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EligibilityResult extends Model
{
    protected $table = 'eligibility_results';

    protected $fillable = [
        'application_id',
        'age_check',
        'nationality_check',
        'education_check',
        'height_check',
        'criminal_check',
        'document_check',
        'marital_check',
        'overall_status',
        'rejection_reasons',
        'ai_confidence',
        'ai_explanation',
        'evaluation_date',
    ];

    protected function casts(): array
    {
        return [
            'age_check' => 'boolean',
            'nationality_check' => 'boolean',
            'education_check' => 'boolean',
            'height_check' => 'boolean',
            'criminal_check' => 'boolean',
            'document_check' => 'boolean',
            'marital_check' => 'boolean',
            'rejection_reasons' => 'array',
            'evaluation_date' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
