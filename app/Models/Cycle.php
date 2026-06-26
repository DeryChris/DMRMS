<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cycle extends Model
{
    protected $table = 'cycles';

    protected $fillable = [
        'name',
        'cycle_code',
        'start_date',
        'end_date',
        'application_deadline',
        'total_vacancies',
        'voucher_price',
        'requirements',
        'ai_enabled',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'ai_enabled' => 'boolean',
            'voucher_price' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'application_deadline' => 'datetime',
        ];
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function aiPredictions(): HasMany
    {
        return $this->hasMany(AiPrediction::class);
    }
}
