<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ApiToken;

class ApiTokenPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ApiToken records.
        return false;
    }

    public function view(User $user, ApiToken $model): bool
    {
        // TODO: Implement business rules for viewing this ApiToken record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ApiToken records.
        return false;
    }

    public function update(User $user, ApiToken $model): bool
    {
        // TODO: Implement business rules for updating this ApiToken record.
        return false;
    }

    public function delete(User $user, ApiToken $model): bool
    {
        // TODO: Implement business rules for deleting this ApiToken record.
        return false;
    }

    public function restore(User $user, ApiToken $model): bool
    {
        // TODO: Implement business rules for restoring this ApiToken record.
        return false;
    }

    public function forceDelete(User $user, ApiToken $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ApiToken record.
        return false;
    }
}
