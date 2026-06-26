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
        'medical_status',
        'medical_notes',
        'medical_data',
        'fitness_result',
        'fitness_score',
        'fitness_details',
        'fitness_notes',
        'interview_result',
        'interview_score',
        'interview_decision',
        'interview_notes',
        'interview_data',
        'overall_status',
        'conducted_by',
        'screened_by',
        'conducted_at',
        'is_stale',
    ];

    protected function casts(): array
    {
        return [
            'conducted_at' => 'datetime',
            'medical_data' => 'array',
            'fitness_details' => 'array',
            'interview_data' => 'array',
            'is_stale' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getMedicalSummaryAttribute(): string
    {
        return $this->medical_data ? json_encode($this->medical_data) : $this->medical_notes ?? 'N/A';
    }

    public function computeOverallStatus(): string
    {
        $medical = $this->medical_result ?? 'pending';
        $fitness = $this->fitness_result ?? 'pending';
        $interview = $this->interview_result ?? 'pending';

        $failureValues = ['unfit', 'fail', 'not_recommended'];
        $successValues = ['fit', 'pass', 'recommended'];

        if (in_array($medical, $failureValues) || in_array($fitness, $failureValues) || in_array($interview, $failureValues)) {
            return 'fail';
        }

        if (in_array($medical, $successValues) && in_array($fitness, $successValues) && in_array($interview, $successValues)) {
            return 'pass';
        }

        if ($medical === 'pending' && $fitness === 'pending' && $interview === 'pending') {
            return 'pending';
        }

        return 'in_progress';
    }

    public function updateOverallStatus(): void
    {
        $this->update(['overall_status' => $this->computeOverallStatus()]);
    }

    public function scopeWhereOverallPass($query)
    {
        return $query->where('overall_status', 'pass');
    }
}
