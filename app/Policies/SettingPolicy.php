<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    public function view(User $user): bool
    {
        return $user->can('manage_settings');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
