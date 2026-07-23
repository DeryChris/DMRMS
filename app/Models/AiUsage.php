<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    protected $table = 'ai_usage';

    protected $fillable = [
        'admin_id',
        'feature',
        'date',
        'total_tokens',
        'tokens_used',
        'total_cost',
        'cost',
        'requests_count',
        'response_time_ms',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_cost' => 'decimal:4',
            'cost' => 'decimal:6',
            'metadata' => 'array',
        ];
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'admin_id');
    }
}
