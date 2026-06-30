<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyOwner;

class PropertyOwnerPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyOwner records.
        return false;
    }

    public function view(User $user, PropertyOwner $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyOwner record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyOwner records.
        return false;
    }

    public function update(User $user, PropertyOwner $model): bool
    {
        // TODO: Implement business rules for updating this PropertyOwner record.
        return false;
    }

    public function delete(User $user, PropertyOwner $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyOwner record.
        return false;
    }

    public function restore(User $user, PropertyOwner $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyOwner record.
        return false;
    }

    public function forceDelete(User $user, PropertyOwner $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyOwner record.
        return false;
    }
}
