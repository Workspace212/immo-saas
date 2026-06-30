<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SubscriptionPlan;

class SubscriptionPlanPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing SubscriptionPlan records.
        return false;
    }

    public function view(User $user, SubscriptionPlan $model): bool
    {
        // TODO: Implement business rules for viewing this SubscriptionPlan record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating SubscriptionPlan records.
        return false;
    }

    public function update(User $user, SubscriptionPlan $model): bool
    {
        // TODO: Implement business rules for updating this SubscriptionPlan record.
        return false;
    }

    public function delete(User $user, SubscriptionPlan $model): bool
    {
        // TODO: Implement business rules for deleting this SubscriptionPlan record.
        return false;
    }

    public function restore(User $user, SubscriptionPlan $model): bool
    {
        // TODO: Implement business rules for restoring this SubscriptionPlan record.
        return false;
    }

    public function forceDelete(User $user, SubscriptionPlan $model): bool
    {
        // TODO: Implement business rules for permanently deleting this SubscriptionPlan record.
        return false;
    }
}
