<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DashboardSnapshot;

class DashboardSnapshotPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing DashboardSnapshot records.
        return false;
    }

    public function view(User $user, DashboardSnapshot $model): bool
    {
        // TODO: Implement business rules for viewing this DashboardSnapshot record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating DashboardSnapshot records.
        return false;
    }

    public function update(User $user, DashboardSnapshot $model): bool
    {
        // TODO: Implement business rules for updating this DashboardSnapshot record.
        return false;
    }

    public function delete(User $user, DashboardSnapshot $model): bool
    {
        // TODO: Implement business rules for deleting this DashboardSnapshot record.
        return false;
    }

    public function restore(User $user, DashboardSnapshot $model): bool
    {
        // TODO: Implement business rules for restoring this DashboardSnapshot record.
        return false;
    }

    public function forceDelete(User $user, DashboardSnapshot $model): bool
    {
        // TODO: Implement business rules for permanently deleting this DashboardSnapshot record.
        return false;
    }
}
