<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tax;

class TaxPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Tax records.
        return false;
    }

    public function view(User $user, Tax $model): bool
    {
        // TODO: Implement business rules for viewing this Tax record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Tax records.
        return false;
    }

    public function update(User $user, Tax $model): bool
    {
        // TODO: Implement business rules for updating this Tax record.
        return false;
    }

    public function delete(User $user, Tax $model): bool
    {
        // TODO: Implement business rules for deleting this Tax record.
        return false;
    }

    public function restore(User $user, Tax $model): bool
    {
        // TODO: Implement business rules for restoring this Tax record.
        return false;
    }

    public function forceDelete(User $user, Tax $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Tax record.
        return false;
    }
}
