<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_users');
    }

    public function view(User $user, User $target): bool
    {
        return $user->can('manage_users');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_users');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('manage_users');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restore(User $user, User $target): bool
    {
        return $user->hasRole('super_admin');
    }
}
