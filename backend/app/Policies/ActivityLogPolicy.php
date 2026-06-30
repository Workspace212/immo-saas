<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActivityLog;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ActivityLog records.
        return false;
    }

    public function view(User $user, ActivityLog $model): bool
    {
        // TODO: Implement business rules for viewing this ActivityLog record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ActivityLog records.
        return false;
    }

    public function update(User $user, ActivityLog $model): bool
    {
        // TODO: Implement business rules for updating this ActivityLog record.
        return false;
    }

    public function delete(User $user, ActivityLog $model): bool
    {
        // TODO: Implement business rules for deleting this ActivityLog record.
        return false;
    }

    public function restore(User $user, ActivityLog $model): bool
    {
        // TODO: Implement business rules for restoring this ActivityLog record.
        return false;
    }

    public function forceDelete(User $user, ActivityLog $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ActivityLog record.
        return false;
    }
}
