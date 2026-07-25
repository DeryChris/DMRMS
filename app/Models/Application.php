<?php

namespace App\Models;

use App\Events\EligibilityPassed;
use App\Events\ScreeningCompleted;
use App\Jobs\AutoRecruit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class Application extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'applicant_id',
        'cycle_id',
        'selected_sector_id',
        'gaf_id',
        'application_date',
        'education_level',
        'institution_name',
        'qualification',
        'degree_field',
        'year_obtained',
        'certificate_number',
        'height',
        'weight',
        'health_conditions',
        'criminal_record',
        'fitness_status',
        'status',
        'current_step',
        'submitted_at',
        'documents_finalized',
        'documents_finalized_at',
        'ai_eligibility_score',
        'ai_ranking_score',
        'ai_verified_at',
        'identity_verification',
        'returned_count',
        'last_returned_from',
        'last_returned_to',
        'last_return_reason',
        'last_returned_at',
        'allocated_corp_id',
        'allocation_status',
    ];

    protected function casts(): array
    {
        return [
            'health_conditions' => 'array',
            'criminal_record' => 'boolean',
            'application_date' => 'datetime',
            'submitted_at' => 'datetime',
            'documents_finalized' => 'boolean',
            'documents_finalized_at' => 'datetime',
            'ai_verified_at' => 'datetime',
            'identity_verification' => 'array',
            'last_returned_at' => 'datetime',
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

        // ──────────────────────────────────────────────────────────────────
        // Central status-change observer — fires on ANY status transition
        // from ANY code path (controllers, commands, jobs, tinker, etc.).
        // The system automatically adapts and proceeds to the next stage.
        // ──────────────────────────────────────────────────────────────────
        static::updated(function (Application $application) {
            if (!$application->wasChanged('status')) {
                return;
            }

            $newStatus = $application->status;

            Log::debug('Application status changed', [
                'id' => $application->id,
                'gaf_id' => $application->gaf_id,
                'new_status' => $newStatus,
            ]);

            match ($newStatus) {
                'eligibility_passed' => EligibilityPassed::dispatch($application),
                'screening_completed' => ScreeningCompleted::dispatch($application),
                'selected' => self::proceedToRecruit($application),
                default => null,
            };
        });
    }

    /**
     * Auto-dispatch recruitment when an applicant is selected.
     * Runs immediately (no 14-day delay) so the demo flows end-to-end.
     */
    private static function proceedToRecruit(Application $application): void
    {
        if (config('recruitment.auto_recruit.enabled', false)) {
            AutoRecruit::dispatch($application);
            Log::info('AutoRecruit dispatched instantly via model observer', [
                'application_id' => $application->id,
                'gaf_id' => $application->gaf_id,
            ]);
        }
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function selectedSector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'selected_sector_id');
    }

    public function corpSelections(): HasMany
    {
        return $this->hasMany(ApplicantCorpSelection::class);
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

    public function reserveList(): HasOne
    {
        return $this->hasOne(ReserveList::class);
    }
}
