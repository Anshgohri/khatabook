<?php

namespace App\Policies;

use App\Models\BankTransaction;
use App\Models\User;

class BankTransactionPolicy
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
    public function view(User $user, BankTransaction $transaction): bool
    {
        if ($user->isAdmin() || $user->isManager() || $user->isViewer()) {
            return true;
        }

        return $transaction->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ! $user->isFinancier() && ! $user->isViewer() && ! $user->isCustomer() && ! $user->isEmployee();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BankTransaction $transaction): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $user->isStaff() && $transaction->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankTransaction $transaction): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
