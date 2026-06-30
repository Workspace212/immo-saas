<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Provider;

class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Provider records.
        return false;
    }

    public function view(User $user, Provider $model): bool
    {
        // TODO: Implement business rules for viewing this Provider record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Provider records.
        return false;
    }

    public function update(User $user, Provider $model): bool
    {
        // TODO: Implement business rules for updating this Provider record.
        return false;
    }

    public function delete(User $user, Provider $model): bool
    {
        // TODO: Implement business rules for deleting this Provider record.
        return false;
    }

    public function restore(User $user, Provider $model): bool
    {
        // TODO: Implement business rules for restoring this Provider record.
        return false;
    }

    public function forceDelete(User $user, Provider $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Provider record.
        return false;
    }
}
