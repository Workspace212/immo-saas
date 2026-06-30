<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LoginHistory;

class LoginHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing LoginHistory records.
        return false;
    }

    public function view(User $user, LoginHistory $model): bool
    {
        // TODO: Implement business rules for viewing this LoginHistory record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating LoginHistory records.
        return false;
    }

    public function update(User $user, LoginHistory $model): bool
    {
        // TODO: Implement business rules for updating this LoginHistory record.
        return false;
    }

    public function delete(User $user, LoginHistory $model): bool
    {
        // TODO: Implement business rules for deleting this LoginHistory record.
        return false;
    }

    public function restore(User $user, LoginHistory $model): bool
    {
        // TODO: Implement business rules for restoring this LoginHistory record.
        return false;
    }

    public function forceDelete(User $user, LoginHistory $model): bool
    {
        // TODO: Implement business rules for permanently deleting this LoginHistory record.
        return false;
    }
}
