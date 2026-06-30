<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RevenueCenter;

class RevenueCenterPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing RevenueCenter records.
        return false;
    }

    public function view(User $user, RevenueCenter $model): bool
    {
        // TODO: Implement business rules for viewing this RevenueCenter record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating RevenueCenter records.
        return false;
    }

    public function update(User $user, RevenueCenter $model): bool
    {
        // TODO: Implement business rules for updating this RevenueCenter record.
        return false;
    }

    public function delete(User $user, RevenueCenter $model): bool
    {
        // TODO: Implement business rules for deleting this RevenueCenter record.
        return false;
    }

    public function restore(User $user, RevenueCenter $model): bool
    {
        // TODO: Implement business rules for restoring this RevenueCenter record.
        return false;
    }

    public function forceDelete(User $user, RevenueCenter $model): bool
    {
        // TODO: Implement business rules for permanently deleting this RevenueCenter record.
        return false;
    }
}
