<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Property;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Property records.
        return false;
    }

    public function view(User $user, Property $model): bool
    {
        // TODO: Implement business rules for viewing this Property record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Property records.
        return false;
    }

    public function update(User $user, Property $model): bool
    {
        // TODO: Implement business rules for updating this Property record.
        return false;
    }

    public function delete(User $user, Property $model): bool
    {
        // TODO: Implement business rules for deleting this Property record.
        return false;
    }

    public function restore(User $user, Property $model): bool
    {
        // TODO: Implement business rules for restoring this Property record.
        return false;
    }

    public function forceDelete(User $user, Property $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Property record.
        return false;
    }
}
