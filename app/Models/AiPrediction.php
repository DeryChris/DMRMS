<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPrediction extends Model
{
    protected $table = 'ai_predictions';

    public $timestamps = false;

    protected $fillable = [
        'cycle_id',
        'prediction_type',
        'predicted_value',
        'confidence',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'predicted_value' => 'array',
            'confidence' => 'decimal:2',
            'generated_at' => 'datetime',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }
}
