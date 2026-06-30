<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ClientPropertyRequest;

class ClientPropertyRequestPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ClientPropertyRequest records.
        return false;
    }

    public function view(User $user, ClientPropertyRequest $model): bool
    {
        // TODO: Implement business rules for viewing this ClientPropertyRequest record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ClientPropertyRequest records.
        return false;
    }

    public function update(User $user, ClientPropertyRequest $model): bool
    {
        // TODO: Implement business rules for updating this ClientPropertyRequest record.
        return false;
    }

    public function delete(User $user, ClientPropertyRequest $model): bool
    {
        // TODO: Implement business rules for deleting this ClientPropertyRequest record.
        return false;
    }

    public function restore(User $user, ClientPropertyRequest $model): bool
    {
        // TODO: Implement business rules for restoring this ClientPropertyRequest record.
        return false;
    }

    public function forceDelete(User $user, ClientPropertyRequest $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ClientPropertyRequest record.
        return false;
    }
}
