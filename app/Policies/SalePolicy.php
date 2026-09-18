<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isFinancier() && ! $user->isCustomer();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sale $sale): bool
    {
        if ($user->isViewer() || $user->isManager() || $user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer()) {
            return $sale->customer_id === $user->id;
        }

        return $sale->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isManager() || $user->isStaff();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): bool
    {
        if ($user->isManager()) {
            return true;
        }

        return $user->isStaff() && $sale->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $user->isManager();
    }
}
