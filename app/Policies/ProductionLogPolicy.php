<?php

namespace App\Policies;

use App\Models\ProductionLog;
use App\Models\User;

class ProductionLogPolicy
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
    public function view(User $user, ProductionLog $productionLog): bool
    {
        return true;
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
    public function update(User $user, ProductionLog $productionLog): bool
    {
        return ! $user->isViewer();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductionLog $productionLog): bool
    {
        return $user->isAdmin();
    }
}
