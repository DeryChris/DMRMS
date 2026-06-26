<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalDecision extends Model
{
    protected $table = 'final_decisions';

    protected $fillable = [
        'application_id',
        'decision',
        'decision_reason',
        'evaluation',
        'committee_members',
        'committee_approved_at',
        'committee_approved_by',
        'decision_date',
        'notification_sent',
        'reporting_code',
        'barrack_id',
    ];

    protected function casts(): array
    {
        return [
            'committee_members' => 'array',
            'evaluation' => 'array',
            'decision_date' => 'datetime',
            'committee_approved_at' => 'datetime',
            'notification_sent' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function barrack(): BelongsTo
    {
        return $this->belongsTo(Barrack::class);
    }
}
