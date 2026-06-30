<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CollaborationOffer;

class CollaborationOfferPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CollaborationOffer records.
        return false;
    }

    public function view(User $user, CollaborationOffer $model): bool
    {
        // TODO: Implement business rules for viewing this CollaborationOffer record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CollaborationOffer records.
        return false;
    }

    public function update(User $user, CollaborationOffer $model): bool
    {
        // TODO: Implement business rules for updating this CollaborationOffer record.
        return false;
    }

    public function delete(User $user, CollaborationOffer $model): bool
    {
        // TODO: Implement business rules for deleting this CollaborationOffer record.
        return false;
    }

    public function restore(User $user, CollaborationOffer $model): bool
    {
        // TODO: Implement business rules for restoring this CollaborationOffer record.
        return false;
    }

    public function forceDelete(User $user, CollaborationOffer $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CollaborationOffer record.
        return false;
    }
}
