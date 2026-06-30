<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Agency;

class AgencyPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Agency records.
        return false;
    }

    public function view(User $user, Agency $model): bool
    {
        // TODO: Implement business rules for viewing this Agency record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Agency records.
        return false;
    }

    public function update(User $user, Agency $model): bool
    {
        // TODO: Implement business rules for updating this Agency record.
        return false;
    }

    public function delete(User $user, Agency $model): bool
    {
        // TODO: Implement business rules for deleting this Agency record.
        return false;
    }

    public function restore(User $user, Agency $model): bool
    {
        // TODO: Implement business rules for restoring this Agency record.
        return false;
    }

    public function forceDelete(User $user, Agency $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Agency record.
        return false;
    }
}
