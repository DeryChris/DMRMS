<?php

namespace App\Policies;

use App\Models\Applicant;
use App\Models\User;

class ApplicantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_applications');
    }

    public function view(User $user, Applicant $applicant): bool
    {
        return $user->can('manage_applications');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_applications');
    }

    public function update(User $user, Applicant $applicant): bool
    {
        return $user->can('manage_applications');
    }

    public function delete(User $user, Applicant $applicant): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restore(User $user, Applicant $applicant): bool
    {
        return $user->hasRole('super_admin');
    }
}
