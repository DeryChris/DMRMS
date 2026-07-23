<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $table = 'administrators';

    protected $fillable = [
        'username',
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
        'password_changed_at',
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
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function setPasswordAttribute($value)
    {
        if (!str_starts_with($value, '$2y$') && !str_starts_with($value, '$2a$') && !str_starts_with($value, '$2b$')) {
            $value = bcrypt($value);
        }
        $this->attributes['password'] = $value;
        $this->attributes['password_changed_at'] = now();
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
        return $this->hasRole('super_admin') || $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        $adminRoles = ['super_admin', 'admin', 'recruitment_officer', 'screening_officer', 'scheduling_officer'];
        return $this->hasAnyRole($adminRoles) || in_array($this->role, $adminRoles);
    }

    public function isRecruitmentOfficer(): bool
    {
        return $this->hasRole('recruitment_officer');
    }

    public function isScreeningOfficer(): bool
    {
        return $this->hasRole('screening_officer');
    }

    public function isSchedulingOfficer(): bool
    {
        return $this->hasRole('scheduling_officer');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isFuture();
    }
}
