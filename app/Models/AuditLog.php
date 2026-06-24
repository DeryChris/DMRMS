<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $guarded = [];

    public $timestamps = true;

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }
}
