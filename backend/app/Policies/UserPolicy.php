<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing User records.
        return false;
    }

    public function view(User $user, User $model): bool
    {
        // TODO: Implement business rules for viewing this User record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating User records.
        return false;
    }

    public function update(User $user, User $model): bool
    {
        // TODO: Implement business rules for updating this User record.
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        // TODO: Implement business rules for deleting this User record.
        return false;
    }

    public function restore(User $user, User $model): bool
    {
        // TODO: Implement business rules for restoring this User record.
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        // TODO: Implement business rules for permanently deleting this User record.
        return false;
    }
}
