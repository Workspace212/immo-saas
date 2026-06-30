<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Client records.
        return false;
    }

    public function view(User $user, Client $model): bool
    {
        // TODO: Implement business rules for viewing this Client record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Client records.
        return false;
    }

    public function update(User $user, Client $model): bool
    {
        // TODO: Implement business rules for updating this Client record.
        return false;
    }

    public function delete(User $user, Client $model): bool
    {
        // TODO: Implement business rules for deleting this Client record.
        return false;
    }

    public function restore(User $user, Client $model): bool
    {
        // TODO: Implement business rules for restoring this Client record.
        return false;
    }

    public function forceDelete(User $user, Client $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Client record.
        return false;
    }
}
