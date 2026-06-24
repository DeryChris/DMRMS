<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Administrator extends Model
{
    use Notifiable;

    protected $table = 'administrators';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'remember_token',
        'role',
        'permissions',
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
            'permissions' => 'array',
            'subscription_expires_at' => 'datetime',
            'last_login' => 'datetime',
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
