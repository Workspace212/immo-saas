<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DashboardFavoriteFilter;

class DashboardFavoriteFilterPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing DashboardFavoriteFilter records.
        return false;
    }

    public function view(User $user, DashboardFavoriteFilter $model): bool
    {
        // TODO: Implement business rules for viewing this DashboardFavoriteFilter record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating DashboardFavoriteFilter records.
        return false;
    }

    public function update(User $user, DashboardFavoriteFilter $model): bool
    {
        // TODO: Implement business rules for updating this DashboardFavoriteFilter record.
        return false;
    }

    public function delete(User $user, DashboardFavoriteFilter $model): bool
    {
        // TODO: Implement business rules for deleting this DashboardFavoriteFilter record.
        return false;
    }

    public function restore(User $user, DashboardFavoriteFilter $model): bool
    {
        // TODO: Implement business rules for restoring this DashboardFavoriteFilter record.
        return false;
    }

    public function forceDelete(User $user, DashboardFavoriteFilter $model): bool
    {
        // TODO: Implement business rules for permanently deleting this DashboardFavoriteFilter record.
        return false;
    }
}
