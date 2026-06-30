<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Subscription records.
        return false;
    }

    public function view(User $user, Subscription $model): bool
    {
        // TODO: Implement business rules for viewing this Subscription record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Subscription records.
        return false;
    }

    public function update(User $user, Subscription $model): bool
    {
        // TODO: Implement business rules for updating this Subscription record.
        return false;
    }

    public function delete(User $user, Subscription $model): bool
    {
        // TODO: Implement business rules for deleting this Subscription record.
        return false;
    }

    public function restore(User $user, Subscription $model): bool
    {
        // TODO: Implement business rules for restoring this Subscription record.
        return false;
    }

    public function forceDelete(User $user, Subscription $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Subscription record.
        return false;
    }
}
