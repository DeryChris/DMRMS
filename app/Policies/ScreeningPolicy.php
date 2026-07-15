<?php

namespace App\Policies;

use App\Models\User;

class ScreeningPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'screening_officer']);
    }

    public function view(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'screening_officer']);
    }

    public function recordMedical(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'screening_officer']);
    }

    public function recordFitness(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'screening_officer']);
    }

    public function recordInterview(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'screening_officer']);
    }

    public function verifyEntry(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'screening_officer']);
    }
}
