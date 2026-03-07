<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Challenge;

class ChallengePolicy
{
    /**
     * Determine if the given user can create challenges.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin; // Assuming 'is_admin' is a boolean column in the users table
    }

    /**
     * Determine if the given challenge can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Challenge  $challenge
     * @return bool
     */
    public function update(User $user, Challenge $challenge)
    {
        return $user->is_admin;
    }
}
