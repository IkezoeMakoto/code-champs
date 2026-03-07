<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the given user can update the specified user.
     *
     * @param  \App\Models\User  $authUser
     * @param  \App\Models\User  $targetUser
     * @return bool
     */
    public function update(User $authUser, User $targetUser)
    {
        return $authUser->is_admin || $authUser->id === $targetUser->id;
    }

}
