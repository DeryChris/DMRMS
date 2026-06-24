<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    protected $table = 'verification_codes';

    protected $fillable = [
        'application_id',
        'code_value',
        'qr_code_path',
        'issue_date',
        'expiry_date',
        'used_status',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'datetime',
            'expiry_date' => 'datetime',
            'used_status' => 'boolean',
            'used_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('used_status', false)
            ->where('expiry_date', '>', now());
    }
}
