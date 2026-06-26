<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'cycle_id',
        'serial_number',
        'pin_code',
        'purchased_at',
        'used_by',
        'used_at',
        'status',
        'expires_at',
        'purchaser_name',
        'purchaser_email',
        'purchaser_phone',
        'payment_method',
        'payment_reference',
        'payment_status',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
            'cost' => 'decimal:2',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'used_by');
    }
}
