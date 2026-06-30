<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSession;

class UserSessionPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing UserSession records.
        return false;
    }

    public function view(User $user, UserSession $model): bool
    {
        // TODO: Implement business rules for viewing this UserSession record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating UserSession records.
        return false;
    }

    public function update(User $user, UserSession $model): bool
    {
        // TODO: Implement business rules for updating this UserSession record.
        return false;
    }

    public function delete(User $user, UserSession $model): bool
    {
        // TODO: Implement business rules for deleting this UserSession record.
        return false;
    }

    public function restore(User $user, UserSession $model): bool
    {
        // TODO: Implement business rules for restoring this UserSession record.
        return false;
    }

    public function forceDelete(User $user, UserSession $model): bool
    {
        // TODO: Implement business rules for permanently deleting this UserSession record.
        return false;
    }
}
