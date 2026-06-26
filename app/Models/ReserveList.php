<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReserveList extends Model
{
    protected $table = 'reserve_lists';

    protected $fillable = [
        'application_id',
        'priority_score',
        'position',
        'notes',
        'promoted_at',
    ];

    protected function casts(): array
    {
        return [
            'promoted_at' => 'datetime',
            'priority_score' => 'integer',
            'position' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeNotPromoted($query)
    {
        return $query->whereNull('promoted_at');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
