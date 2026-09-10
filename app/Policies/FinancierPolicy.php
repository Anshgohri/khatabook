<?php

namespace App\Policies;

use App\Models\Financier;
use App\Models\User;

class FinancierPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Financier $financier): bool
    {
        return $financier->user_id === $user->id || $user->isManager();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ! $user->isViewer();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Financier $financier): bool
    {
        return $financier->user_id === $user->id || $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Financier $financier): bool
    {
        return $user->isAdmin();
    }
}
