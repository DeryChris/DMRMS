<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_applications');
    }

    public function view(User $user, Application $application): bool
    {
        return $user->can('manage_applications');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_applications');
    }

    public function update(User $user, Application $application): bool
    {
        return $user->can('manage_applications');
    }

    public function verify(User $user, Application $application): bool
    {
        return $user->can('manage_applications');
    }

    public function shortlist(User $user, Application $application): bool
    {
        return $user->can('manage_applications');
    }

    public function sendBack(User $user, Application $application): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'recruitment_officer']);
    }
}
