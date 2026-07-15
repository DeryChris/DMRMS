<?php

namespace App\Policies;

use App\Models\Cycle;
use App\Models\User;

class CyclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_cycles');
    }

    public function view(User $user, Cycle $cycle): bool
    {
        return $user->can('manage_cycles');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'recruitment_officer']);
    }

    public function update(User $user, Cycle $cycle): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'recruitment_officer']);
    }

    public function delete(User $user, Cycle $cycle): bool
    {
        return $user->hasRole('super_admin');
    }
}
