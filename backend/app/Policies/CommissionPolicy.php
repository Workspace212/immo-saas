<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Commission;

class CommissionPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Commission records.
        return false;
    }

    public function view(User $user, Commission $model): bool
    {
        // TODO: Implement business rules for viewing this Commission record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Commission records.
        return false;
    }

    public function update(User $user, Commission $model): bool
    {
        // TODO: Implement business rules for updating this Commission record.
        return false;
    }

    public function delete(User $user, Commission $model): bool
    {
        // TODO: Implement business rules for deleting this Commission record.
        return false;
    }

    public function restore(User $user, Commission $model): bool
    {
        // TODO: Implement business rules for restoring this Commission record.
        return false;
    }

    public function forceDelete(User $user, Commission $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Commission record.
        return false;
    }
}
