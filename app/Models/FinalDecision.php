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
        'committee_members',
        'decision_date',
        'notification_sent',
    ];

    protected function casts(): array
    {
        return [
            'committee_members' => 'array',
            'decision_date' => 'datetime',
            'notification_sent' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
