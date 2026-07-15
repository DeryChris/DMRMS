<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Applicant extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'applicants';

    protected $fillable = [
        'voucher_id',
        'first_name',
        'last_name',
        'other_names',
        'date_of_birth',
        'gender',
        'marital_status',
        'contact_number',
        'alternative_contact',
        'email',
        'residential_address',
        'region',
        'district',
        'nationality',
        'national_id',
        'password',
        'remember_token',
        'email_verified_at',
        'email_verification_code',
        'email_verification_sent_at',
        'phone_verified',
        'status',
        'last_login',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'email_verified_at' => 'datetime',
            'email_verification_sent_at' => 'datetime',
            'phone_verified' => 'boolean',
            'last_login' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function setGenderAttribute($value)
    {
        $this->attributes['gender'] = strtolower($value);
    }

    public function setPasswordAttribute($value)
    {
        if (!str_starts_with($value, '$2y$') && !str_starts_with($value, '$2a$') && !str_starts_with($value, '$2b$')) {
            $value = bcrypt($value);
        }
        $this->attributes['password'] = $value;
        $this->attributes['password_changed_at'] = now();
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function application(): HasOne
    {
        return $this->hasOne(Application::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'applicant_id');
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class, 'applicant_id');
    }
}
