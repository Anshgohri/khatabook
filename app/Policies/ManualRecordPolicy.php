<?php

namespace App\Policies;

use App\Models\ManualRecord;
use App\Models\User;

class ManualRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ! $user->isCustomer() && ! $user->isEmployee() && ! $user->isFinancier() && ! $user->isSupplier();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ManualRecord $record): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->viewAny($user) && ! $user->isViewer();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?ManualRecord $record = null): bool
    {
        return $this->viewAny($user) && ! $user->isViewer();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ManualRecord $record): bool
    {
        return $this->viewAny($user) && ! $user->isViewer();
    }
}
