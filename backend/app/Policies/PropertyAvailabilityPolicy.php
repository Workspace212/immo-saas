<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyAvailability;

class PropertyAvailabilityPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyAvailability records.
        return false;
    }

    public function view(User $user, PropertyAvailability $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyAvailability record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyAvailability records.
        return false;
    }

    public function update(User $user, PropertyAvailability $model): bool
    {
        // TODO: Implement business rules for updating this PropertyAvailability record.
        return false;
    }

    public function delete(User $user, PropertyAvailability $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyAvailability record.
        return false;
    }

    public function restore(User $user, PropertyAvailability $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyAvailability record.
        return false;
    }

    public function forceDelete(User $user, PropertyAvailability $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyAvailability record.
        return false;
    }
}
