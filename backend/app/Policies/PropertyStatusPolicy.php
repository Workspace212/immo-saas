<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyStatus;

class PropertyStatusPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyStatus records.
        return false;
    }

    public function view(User $user, PropertyStatus $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyStatus record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyStatus records.
        return false;
    }

    public function update(User $user, PropertyStatus $model): bool
    {
        // TODO: Implement business rules for updating this PropertyStatus record.
        return false;
    }

    public function delete(User $user, PropertyStatus $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyStatus record.
        return false;
    }

    public function restore(User $user, PropertyStatus $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyStatus record.
        return false;
    }

    public function forceDelete(User $user, PropertyStatus $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyStatus record.
        return false;
    }
}
