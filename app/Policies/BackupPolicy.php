<?php

namespace App\Policies;

use App\Models\User;

class BackupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_backups');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_backups');
    }

    public function download(User $user): bool
    {
        return $user->can('manage_backups');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
