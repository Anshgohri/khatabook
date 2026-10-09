<?php

namespace App\Policies;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RentalPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isFinancier() && ! $user->isCustomer();
    }

    public function view(User $user, Rental $rental): bool
    {
        if ($user->isViewer() || $user->isManager() || $user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer()) {
            return $rental->customer_id === $user->id;
        }

        return $rental->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isManager() || $user->isStaff() || $user->isEmployee();
    }

    public function update(User $user, Rental $rental): bool
    {
        if ($user->isManager()) {
            return true;
        }

        return ($user->isStaff() || $user->isEmployee()) && $rental->user_id === $user->id;
    }

    public function delete(User $user, Rental $rental): bool
    {
        return $user->isManager();
    }
}
