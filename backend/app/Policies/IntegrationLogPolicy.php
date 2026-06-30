<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IntegrationLog;

class IntegrationLogPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing IntegrationLog records.
        return false;
    }

    public function view(User $user, IntegrationLog $model): bool
    {
        // TODO: Implement business rules for viewing this IntegrationLog record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating IntegrationLog records.
        return false;
    }

    public function update(User $user, IntegrationLog $model): bool
    {
        // TODO: Implement business rules for updating this IntegrationLog record.
        return false;
    }

    public function delete(User $user, IntegrationLog $model): bool
    {
        // TODO: Implement business rules for deleting this IntegrationLog record.
        return false;
    }

    public function restore(User $user, IntegrationLog $model): bool
    {
        // TODO: Implement business rules for restoring this IntegrationLog record.
        return false;
    }

    public function forceDelete(User $user, IntegrationLog $model): bool
    {
        // TODO: Implement business rules for permanently deleting this IntegrationLog record.
        return false;
    }
}
