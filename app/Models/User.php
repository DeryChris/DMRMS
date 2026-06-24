<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'administrators';

    protected $fillable = [
        'username',
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
            'password' => 'hashed',
        ];
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'admin_id');
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'admin_id')->whereNull('read_at');
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'recruitment_officer', 'screening_officer', 'scheduling_officer']);
    }

    public function isRecruitmentOfficer(): bool
    {
        return $this->role === 'recruitment_officer';
    }

    public function isScreeningOfficer(): bool
    {
        return $this->role === 'screening_officer';
    }

    public function isSchedulingOfficer(): bool
    {
        return $this->role === 'scheduling_officer';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isFuture();
    }
}
