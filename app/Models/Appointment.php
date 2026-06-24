<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $table = 'appointments';

    protected $fillable = [
        'application_id',
        'scheduled_date',
        'scheduled_time',
        'venue',
        'slot_number',
        'status',
        'notification_sent',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'scheduled_time' => 'string',
            'notification_sent' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
