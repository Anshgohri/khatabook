<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ! $user->isFinancier() && ! $user->isCustomer() && ! $user->isEmployee();
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        if ($employee->user_id === $user->id || $employee->employee_user_id === $user->id) {
            return true;
        }

        if (! empty($user->phone) && $employee->phone === $user->phone) {
            return true;
        }

        if (! empty($user->email) && $employee->email === $user->email) {
            return true;
        }

        return false;
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
    public function update(User $user, Employee $employee): bool
    {
        return $employee->user_id === $user->id || $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->isAdmin();
    }
}
