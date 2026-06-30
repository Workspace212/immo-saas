<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AppNotification;

class AppNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing AppNotification records.
        return false;
    }

    public function view(User $user, AppNotification $model): bool
    {
        // TODO: Implement business rules for viewing this AppNotification record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating AppNotification records.
        return false;
    }

    public function update(User $user, AppNotification $model): bool
    {
        // TODO: Implement business rules for updating this AppNotification record.
        return false;
    }

    public function delete(User $user, AppNotification $model): bool
    {
        // TODO: Implement business rules for deleting this AppNotification record.
        return false;
    }

    public function restore(User $user, AppNotification $model): bool
    {
        // TODO: Implement business rules for restoring this AppNotification record.
        return false;
    }

    public function forceDelete(User $user, AppNotification $model): bool
    {
        // TODO: Implement business rules for permanently deleting this AppNotification record.
        return false;
    }
}
