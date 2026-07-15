<?php

namespace App\Policies;

use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_scheduling');
    }

    public function schedule(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'scheduling_officer']);
    }

    public function reschedule(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'scheduling_officer']);
    }

    public function markAttendance(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'screening_officer']);
    }
}
