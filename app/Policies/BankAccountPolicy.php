<?php

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\User;

class BankAccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ! $user->isFinancier() && ! $user->isCustomer() && ! $user->isEmployee();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BankAccount $account): bool
    {
        return ! $user->isFinancier() && ! $user->isCustomer() && ! $user->isEmployee();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BankAccount $account): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankAccount $account): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
