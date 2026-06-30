<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CollaborationMessage;

class CollaborationMessagePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CollaborationMessage records.
        return false;
    }

    public function view(User $user, CollaborationMessage $model): bool
    {
        // TODO: Implement business rules for viewing this CollaborationMessage record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CollaborationMessage records.
        return false;
    }

    public function update(User $user, CollaborationMessage $model): bool
    {
        // TODO: Implement business rules for updating this CollaborationMessage record.
        return false;
    }

    public function delete(User $user, CollaborationMessage $model): bool
    {
        // TODO: Implement business rules for deleting this CollaborationMessage record.
        return false;
    }

    public function restore(User $user, CollaborationMessage $model): bool
    {
        // TODO: Implement business rules for restoring this CollaborationMessage record.
        return false;
    }

    public function forceDelete(User $user, CollaborationMessage $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CollaborationMessage record.
        return false;
    }
}
