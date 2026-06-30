<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ComplaintProvider;

class ComplaintProviderPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ComplaintProvider records.
        return false;
    }

    public function view(User $user, ComplaintProvider $model): bool
    {
        // TODO: Implement business rules for viewing this ComplaintProvider record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ComplaintProvider records.
        return false;
    }

    public function update(User $user, ComplaintProvider $model): bool
    {
        // TODO: Implement business rules for updating this ComplaintProvider record.
        return false;
    }

    public function delete(User $user, ComplaintProvider $model): bool
    {
        // TODO: Implement business rules for deleting this ComplaintProvider record.
        return false;
    }

    public function restore(User $user, ComplaintProvider $model): bool
    {
        // TODO: Implement business rules for restoring this ComplaintProvider record.
        return false;
    }

    public function forceDelete(User $user, ComplaintProvider $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ComplaintProvider record.
        return false;
    }
}
