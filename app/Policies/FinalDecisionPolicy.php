<?php

namespace App\Policies;

use App\Models\User;

class FinalDecisionPolicy
{
    public function create(User $user): bool
    {
        return $user->can('manage_selection');
    }

    public function finalize(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }
}
