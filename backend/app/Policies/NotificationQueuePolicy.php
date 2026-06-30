<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NotificationQueue;

class NotificationQueuePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing NotificationQueue records.
        return false;
    }

    public function view(User $user, NotificationQueue $model): bool
    {
        // TODO: Implement business rules for viewing this NotificationQueue record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating NotificationQueue records.
        return false;
    }

    public function update(User $user, NotificationQueue $model): bool
    {
        // TODO: Implement business rules for updating this NotificationQueue record.
        return false;
    }

    public function delete(User $user, NotificationQueue $model): bool
    {
        // TODO: Implement business rules for deleting this NotificationQueue record.
        return false;
    }

    public function restore(User $user, NotificationQueue $model): bool
    {
        // TODO: Implement business rules for restoring this NotificationQueue record.
        return false;
    }

    public function forceDelete(User $user, NotificationQueue $model): bool
    {
        // TODO: Implement business rules for permanently deleting this NotificationQueue record.
        return false;
    }
}
