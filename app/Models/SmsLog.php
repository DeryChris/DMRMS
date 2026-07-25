<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'phone',
        'message',
        'is_otp',
        'campaign_id',
        'status',
        'provider_response',
        'cost',
        'sent_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_otp'       => 'boolean',
            'cost'         => 'decimal:4',
            'sent_at'      => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOtp($query)
    {
        return $query->where('is_otp', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByDate($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
