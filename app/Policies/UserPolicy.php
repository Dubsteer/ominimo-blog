<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function updateRole(User $actor, User $user): bool
    {
        return $actor->isAdministrator()
            && ! $actor->is($user);
    }
}
