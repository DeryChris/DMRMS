<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'applicant_id',
        'cycle_id',
        'gaf_id',
        'application_date',
        'education_level',
        'institution_name',
        'qualification',
        'year_obtained',
        'certificate_number',
        'height',
        'weight',
        'health_conditions',
        'criminal_record',
        'fitness_status',
        'status',
        'submitted_at',
        'ai_eligibility_score',
        'ai_ranking_score',
        'ai_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'health_conditions' => 'array',
            'criminal_record' => 'boolean',
            'application_date' => 'datetime',
            'submitted_at' => 'datetime',
            'ai_verified_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($application) {
            if (!$application->gaf_id) {
                $latest = static::max('id') ?? 0;
                $application->gaf_id = 'GAF-' . date('Y') . '-' . str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function eligibilityResult(): HasOne
    {
        return $this->hasOne(EligibilityResult::class);
    }

    public function eligibility(): HasOne
    {
        return $this->eligibilityResult();
    }

    public function verificationCode(): HasOne
    {
        return $this->hasOne(VerificationCode::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    public function screeningResult(): HasOne
    {
        return $this->hasOne(ScreeningResult::class);
    }

    public function finalDecision(): HasOne
    {
        return $this->hasOne(FinalDecision::class);
    }
}
