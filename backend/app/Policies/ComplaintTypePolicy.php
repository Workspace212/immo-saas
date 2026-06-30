<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ComplaintType;

class ComplaintTypePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ComplaintType records.
        return false;
    }

    public function view(User $user, ComplaintType $model): bool
    {
        // TODO: Implement business rules for viewing this ComplaintType record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ComplaintType records.
        return false;
    }

    public function update(User $user, ComplaintType $model): bool
    {
        // TODO: Implement business rules for updating this ComplaintType record.
        return false;
    }

    public function delete(User $user, ComplaintType $model): bool
    {
        // TODO: Implement business rules for deleting this ComplaintType record.
        return false;
    }

    public function restore(User $user, ComplaintType $model): bool
    {
        // TODO: Implement business rules for restoring this ComplaintType record.
        return false;
    }

    public function forceDelete(User $user, ComplaintType $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ComplaintType record.
        return false;
    }
}
