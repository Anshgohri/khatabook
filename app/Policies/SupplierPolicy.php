<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ! $user->isCustomer() && ! $user->isEmployee();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return $supplier->user_id === $user->id || $supplier->supplier_user_id === $user->id || $user->isManager();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ! $user->isViewer() && ! $user->isSupplier();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?Supplier $supplier = null): bool
    {
        if ($supplier === null) {
            return ! $user->isViewer() && ! $user->isCustomer() && ! $user->isEmployee() && ! $user->isSupplier();
        }

        return $supplier->user_id === $user->id || $supplier->supplier_user_id === $user->id || $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->isAdmin();
    }
}
