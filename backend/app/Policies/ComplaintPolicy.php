<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Complaint;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Complaint records.
        return false;
    }

    public function view(User $user, Complaint $model): bool
    {
        // TODO: Implement business rules for viewing this Complaint record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Complaint records.
        return false;
    }

    public function update(User $user, Complaint $model): bool
    {
        // TODO: Implement business rules for updating this Complaint record.
        return false;
    }

    public function delete(User $user, Complaint $model): bool
    {
        // TODO: Implement business rules for deleting this Complaint record.
        return false;
    }

    public function restore(User $user, Complaint $model): bool
    {
        // TODO: Implement business rules for restoring this Complaint record.
        return false;
    }

    public function forceDelete(User $user, Complaint $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Complaint record.
        return false;
    }
}
