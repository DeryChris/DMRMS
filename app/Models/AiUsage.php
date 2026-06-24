<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    protected $table = 'ai_usage';

    protected $fillable = [
        'admin_id',
        'date',
        'total_tokens',
        'total_cost',
        'requests_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_cost' => 'decimal:4',
        ];
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'admin_id');
    }
}
