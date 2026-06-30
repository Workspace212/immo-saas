<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NotificationPreference;

class NotificationPreferencePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing NotificationPreference records.
        return false;
    }

    public function view(User $user, NotificationPreference $model): bool
    {
        // TODO: Implement business rules for viewing this NotificationPreference record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating NotificationPreference records.
        return false;
    }

    public function update(User $user, NotificationPreference $model): bool
    {
        // TODO: Implement business rules for updating this NotificationPreference record.
        return false;
    }

    public function delete(User $user, NotificationPreference $model): bool
    {
        // TODO: Implement business rules for deleting this NotificationPreference record.
        return false;
    }

    public function restore(User $user, NotificationPreference $model): bool
    {
        // TODO: Implement business rules for restoring this NotificationPreference record.
        return false;
    }

    public function forceDelete(User $user, NotificationPreference $model): bool
    {
        // TODO: Implement business rules for permanently deleting this NotificationPreference record.
        return false;
    }
}
