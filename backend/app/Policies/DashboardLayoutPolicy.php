<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DashboardLayout;

class DashboardLayoutPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing DashboardLayout records.
        return false;
    }

    public function view(User $user, DashboardLayout $model): bool
    {
        // TODO: Implement business rules for viewing this DashboardLayout record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating DashboardLayout records.
        return false;
    }

    public function update(User $user, DashboardLayout $model): bool
    {
        // TODO: Implement business rules for updating this DashboardLayout record.
        return false;
    }

    public function delete(User $user, DashboardLayout $model): bool
    {
        // TODO: Implement business rules for deleting this DashboardLayout record.
        return false;
    }

    public function restore(User $user, DashboardLayout $model): bool
    {
        // TODO: Implement business rules for restoring this DashboardLayout record.
        return false;
    }

    public function forceDelete(User $user, DashboardLayout $model): bool
    {
        // TODO: Implement business rules for permanently deleting this DashboardLayout record.
        return false;
    }
}
