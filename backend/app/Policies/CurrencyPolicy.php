<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Currency;

class CurrencyPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Currency records.
        return false;
    }

    public function view(User $user, Currency $model): bool
    {
        // TODO: Implement business rules for viewing this Currency record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Currency records.
        return false;
    }

    public function update(User $user, Currency $model): bool
    {
        // TODO: Implement business rules for updating this Currency record.
        return false;
    }

    public function delete(User $user, Currency $model): bool
    {
        // TODO: Implement business rules for deleting this Currency record.
        return false;
    }

    public function restore(User $user, Currency $model): bool
    {
        // TODO: Implement business rules for restoring this Currency record.
        return false;
    }

    public function forceDelete(User $user, Currency $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Currency record.
        return false;
    }
}
