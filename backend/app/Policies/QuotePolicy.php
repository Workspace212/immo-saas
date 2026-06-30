<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quote;

class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Quote records.
        return false;
    }

    public function view(User $user, Quote $model): bool
    {
        // TODO: Implement business rules for viewing this Quote record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Quote records.
        return false;
    }

    public function update(User $user, Quote $model): bool
    {
        // TODO: Implement business rules for updating this Quote record.
        return false;
    }

    public function delete(User $user, Quote $model): bool
    {
        // TODO: Implement business rules for deleting this Quote record.
        return false;
    }

    public function restore(User $user, Quote $model): bool
    {
        // TODO: Implement business rules for restoring this Quote record.
        return false;
    }

    public function forceDelete(User $user, Quote $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Quote record.
        return false;
    }
}
