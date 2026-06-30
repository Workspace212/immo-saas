<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CashMovement;

class CashMovementPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CashMovement records.
        return false;
    }

    public function view(User $user, CashMovement $model): bool
    {
        // TODO: Implement business rules for viewing this CashMovement record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CashMovement records.
        return false;
    }

    public function update(User $user, CashMovement $model): bool
    {
        // TODO: Implement business rules for updating this CashMovement record.
        return false;
    }

    public function delete(User $user, CashMovement $model): bool
    {
        // TODO: Implement business rules for deleting this CashMovement record.
        return false;
    }

    public function restore(User $user, CashMovement $model): bool
    {
        // TODO: Implement business rules for restoring this CashMovement record.
        return false;
    }

    public function forceDelete(User $user, CashMovement $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CashMovement record.
        return false;
    }
}
