<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NumberSequence;

class NumberSequencePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing NumberSequence records.
        return false;
    }

    public function view(User $user, NumberSequence $model): bool
    {
        // TODO: Implement business rules for viewing this NumberSequence record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating NumberSequence records.
        return false;
    }

    public function update(User $user, NumberSequence $model): bool
    {
        // TODO: Implement business rules for updating this NumberSequence record.
        return false;
    }

    public function delete(User $user, NumberSequence $model): bool
    {
        // TODO: Implement business rules for deleting this NumberSequence record.
        return false;
    }

    public function restore(User $user, NumberSequence $model): bool
    {
        // TODO: Implement business rules for restoring this NumberSequence record.
        return false;
    }

    public function forceDelete(User $user, NumberSequence $model): bool
    {
        // TODO: Implement business rules for permanently deleting this NumberSequence record.
        return false;
    }
}
