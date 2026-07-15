<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Administrator extends Model
{
    use Notifiable, SoftDeletes;

    protected $table = 'administrators';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'remember_token',
        'role',
        'legacy_permissions',
        'subscription_tier',
        'subscription_expires_at',
        'ai_usage_limit',
        'status',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'legacy_permissions' => 'array',
            'subscription_expires_at' => 'datetime',
            'last_login' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super_admin';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isFuture();
    }
}
