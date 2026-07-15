<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorpEducationRequirement extends Model
{
    protected $table = 'corp_education_requirements';

    protected $fillable = [
        'corp_id',
        'education_level_id',
        'degree_field_group',
        'specific_degrees',
        'additional_certs',
        'min_grade',
    ];

    protected function casts(): array
    {
        return [
            'specific_degrees' => 'array',
            'additional_certs' => 'array',
        ];
    }

    public function corp(): BelongsTo
    {
        return $this->belongsTo(Corp::class);
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }
}
