<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_applications');
    }

    public function view(User $user, Document $document): bool
    {
        return $user->can('manage_applications');
    }

    public function verify(User $user, Document $document): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'recruitment_officer', 'screening_officer']);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole('super_admin');
    }
}
