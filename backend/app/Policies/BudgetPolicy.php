<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Budget;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Budget records.
        return false;
    }

    public function view(User $user, Budget $model): bool
    {
        // TODO: Implement business rules for viewing this Budget record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Budget records.
        return false;
    }

    public function update(User $user, Budget $model): bool
    {
        // TODO: Implement business rules for updating this Budget record.
        return false;
    }

    public function delete(User $user, Budget $model): bool
    {
        // TODO: Implement business rules for deleting this Budget record.
        return false;
    }

    public function restore(User $user, Budget $model): bool
    {
        // TODO: Implement business rules for restoring this Budget record.
        return false;
    }

    public function forceDelete(User $user, Budget $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Budget record.
        return false;
    }
}
