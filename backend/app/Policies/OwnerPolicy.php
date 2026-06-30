<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Owner;

class OwnerPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Owner records.
        return false;
    }

    public function view(User $user, Owner $model): bool
    {
        // TODO: Implement business rules for viewing this Owner record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Owner records.
        return false;
    }

    public function update(User $user, Owner $model): bool
    {
        // TODO: Implement business rules for updating this Owner record.
        return false;
    }

    public function delete(User $user, Owner $model): bool
    {
        // TODO: Implement business rules for deleting this Owner record.
        return false;
    }

    public function restore(User $user, Owner $model): bool
    {
        // TODO: Implement business rules for restoring this Owner record.
        return false;
    }

    public function forceDelete(User $user, Owner $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Owner record.
        return false;
    }
}
