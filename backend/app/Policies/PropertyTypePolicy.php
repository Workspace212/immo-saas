<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyType;

class PropertyTypePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyType records.
        return false;
    }

    public function view(User $user, PropertyType $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyType record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyType records.
        return false;
    }

    public function update(User $user, PropertyType $model): bool
    {
        // TODO: Implement business rules for updating this PropertyType record.
        return false;
    }

    public function delete(User $user, PropertyType $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyType record.
        return false;
    }

    public function restore(User $user, PropertyType $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyType record.
        return false;
    }

    public function forceDelete(User $user, PropertyType $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyType record.
        return false;
    }
}
