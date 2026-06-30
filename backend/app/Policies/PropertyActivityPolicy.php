<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyActivity;

class PropertyActivityPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyActivity records.
        return false;
    }

    public function view(User $user, PropertyActivity $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyActivity record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyActivity records.
        return false;
    }

    public function update(User $user, PropertyActivity $model): bool
    {
        // TODO: Implement business rules for updating this PropertyActivity record.
        return false;
    }

    public function delete(User $user, PropertyActivity $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyActivity record.
        return false;
    }

    public function restore(User $user, PropertyActivity $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyActivity record.
        return false;
    }

    public function forceDelete(User $user, PropertyActivity $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyActivity record.
        return false;
    }
}
