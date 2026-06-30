<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Integration;

class IntegrationPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Integration records.
        return false;
    }

    public function view(User $user, Integration $model): bool
    {
        // TODO: Implement business rules for viewing this Integration record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Integration records.
        return false;
    }

    public function update(User $user, Integration $model): bool
    {
        // TODO: Implement business rules for updating this Integration record.
        return false;
    }

    public function delete(User $user, Integration $model): bool
    {
        // TODO: Implement business rules for deleting this Integration record.
        return false;
    }

    public function restore(User $user, Integration $model): bool
    {
        // TODO: Implement business rules for restoring this Integration record.
        return false;
    }

    public function forceDelete(User $user, Integration $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Integration record.
        return false;
    }
}
