<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NotificationTemplate;

class NotificationTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing NotificationTemplate records.
        return false;
    }

    public function view(User $user, NotificationTemplate $model): bool
    {
        // TODO: Implement business rules for viewing this NotificationTemplate record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating NotificationTemplate records.
        return false;
    }

    public function update(User $user, NotificationTemplate $model): bool
    {
        // TODO: Implement business rules for updating this NotificationTemplate record.
        return false;
    }

    public function delete(User $user, NotificationTemplate $model): bool
    {
        // TODO: Implement business rules for deleting this NotificationTemplate record.
        return false;
    }

    public function restore(User $user, NotificationTemplate $model): bool
    {
        // TODO: Implement business rules for restoring this NotificationTemplate record.
        return false;
    }

    public function forceDelete(User $user, NotificationTemplate $model): bool
    {
        // TODO: Implement business rules for permanently deleting this NotificationTemplate record.
        return false;
    }
}
