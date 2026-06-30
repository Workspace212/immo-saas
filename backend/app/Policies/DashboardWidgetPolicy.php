<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DashboardWidget;

class DashboardWidgetPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing DashboardWidget records.
        return false;
    }

    public function view(User $user, DashboardWidget $model): bool
    {
        // TODO: Implement business rules for viewing this DashboardWidget record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating DashboardWidget records.
        return false;
    }

    public function update(User $user, DashboardWidget $model): bool
    {
        // TODO: Implement business rules for updating this DashboardWidget record.
        return false;
    }

    public function delete(User $user, DashboardWidget $model): bool
    {
        // TODO: Implement business rules for deleting this DashboardWidget record.
        return false;
    }

    public function restore(User $user, DashboardWidget $model): bool
    {
        // TODO: Implement business rules for restoring this DashboardWidget record.
        return false;
    }

    public function forceDelete(User $user, DashboardWidget $model): bool
    {
        // TODO: Implement business rules for permanently deleting this DashboardWidget record.
        return false;
    }
}
