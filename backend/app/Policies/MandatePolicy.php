<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mandate;

class MandatePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Mandate records.
        return false;
    }

    public function view(User $user, Mandate $model): bool
    {
        // TODO: Implement business rules for viewing this Mandate record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Mandate records.
        return false;
    }

    public function update(User $user, Mandate $model): bool
    {
        // TODO: Implement business rules for updating this Mandate record.
        return false;
    }

    public function delete(User $user, Mandate $model): bool
    {
        // TODO: Implement business rules for deleting this Mandate record.
        return false;
    }

    public function restore(User $user, Mandate $model): bool
    {
        // TODO: Implement business rules for restoring this Mandate record.
        return false;
    }

    public function forceDelete(User $user, Mandate $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Mandate record.
        return false;
    }
}
