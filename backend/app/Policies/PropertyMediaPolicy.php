<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyMedia;

class PropertyMediaPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyMedia records.
        return false;
    }

    public function view(User $user, PropertyMedia $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyMedia record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyMedia records.
        return false;
    }

    public function update(User $user, PropertyMedia $model): bool
    {
        // TODO: Implement business rules for updating this PropertyMedia record.
        return false;
    }

    public function delete(User $user, PropertyMedia $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyMedia record.
        return false;
    }

    public function restore(User $user, PropertyMedia $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyMedia record.
        return false;
    }

    public function forceDelete(User $user, PropertyMedia $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyMedia record.
        return false;
    }
}
