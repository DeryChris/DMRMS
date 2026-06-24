<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningResult extends Model
{
    protected $table = 'screening_results';

    protected $fillable = [
        'application_id',
        'medical_result',
        'medical_notes',
        'fitness_result',
        'fitness_score',
        'interview_result',
        'interview_notes',
        'overall_status',
        'conducted_by',
        'conducted_at',
    ];

    protected function casts(): array
    {
        return [
            'conducted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
