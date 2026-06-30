<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ApiWebhookLog;

class ApiWebhookLogPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ApiWebhookLog records.
        return false;
    }

    public function view(User $user, ApiWebhookLog $model): bool
    {
        // TODO: Implement business rules for viewing this ApiWebhookLog record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ApiWebhookLog records.
        return false;
    }

    public function update(User $user, ApiWebhookLog $model): bool
    {
        // TODO: Implement business rules for updating this ApiWebhookLog record.
        return false;
    }

    public function delete(User $user, ApiWebhookLog $model): bool
    {
        // TODO: Implement business rules for deleting this ApiWebhookLog record.
        return false;
    }

    public function restore(User $user, ApiWebhookLog $model): bool
    {
        // TODO: Implement business rules for restoring this ApiWebhookLog record.
        return false;
    }

    public function forceDelete(User $user, ApiWebhookLog $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ApiWebhookLog record.
        return false;
    }
}
