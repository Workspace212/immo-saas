<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CollaborationVisit;

class CollaborationVisitPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CollaborationVisit records.
        return false;
    }

    public function view(User $user, CollaborationVisit $model): bool
    {
        // TODO: Implement business rules for viewing this CollaborationVisit record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CollaborationVisit records.
        return false;
    }

    public function update(User $user, CollaborationVisit $model): bool
    {
        // TODO: Implement business rules for updating this CollaborationVisit record.
        return false;
    }

    public function delete(User $user, CollaborationVisit $model): bool
    {
        // TODO: Implement business rules for deleting this CollaborationVisit record.
        return false;
    }

    public function restore(User $user, CollaborationVisit $model): bool
    {
        // TODO: Implement business rules for restoring this CollaborationVisit record.
        return false;
    }

    public function forceDelete(User $user, CollaborationVisit $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CollaborationVisit record.
        return false;
    }
}
