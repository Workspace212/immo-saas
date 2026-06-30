<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ApiWebhook;

class ApiWebhookPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ApiWebhook records.
        return false;
    }

    public function view(User $user, ApiWebhook $model): bool
    {
        // TODO: Implement business rules for viewing this ApiWebhook record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ApiWebhook records.
        return false;
    }

    public function update(User $user, ApiWebhook $model): bool
    {
        // TODO: Implement business rules for updating this ApiWebhook record.
        return false;
    }

    public function delete(User $user, ApiWebhook $model): bool
    {
        // TODO: Implement business rules for deleting this ApiWebhook record.
        return false;
    }

    public function restore(User $user, ApiWebhook $model): bool
    {
        // TODO: Implement business rules for restoring this ApiWebhook record.
        return false;
    }

    public function forceDelete(User $user, ApiWebhook $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ApiWebhook record.
        return false;
    }
}
