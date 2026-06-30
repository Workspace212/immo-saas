<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SystemEvent;

class SystemEventPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing SystemEvent records.
        return false;
    }

    public function view(User $user, SystemEvent $model): bool
    {
        // TODO: Implement business rules for viewing this SystemEvent record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating SystemEvent records.
        return false;
    }

    public function update(User $user, SystemEvent $model): bool
    {
        // TODO: Implement business rules for updating this SystemEvent record.
        return false;
    }

    public function delete(User $user, SystemEvent $model): bool
    {
        // TODO: Implement business rules for deleting this SystemEvent record.
        return false;
    }

    public function restore(User $user, SystemEvent $model): bool
    {
        // TODO: Implement business rules for restoring this SystemEvent record.
        return false;
    }

    public function forceDelete(User $user, SystemEvent $model): bool
    {
        // TODO: Implement business rules for permanently deleting this SystemEvent record.
        return false;
    }
}
