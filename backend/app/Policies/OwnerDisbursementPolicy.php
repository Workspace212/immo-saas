<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OwnerDisbursement;

class OwnerDisbursementPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing OwnerDisbursement records.
        return false;
    }

    public function view(User $user, OwnerDisbursement $model): bool
    {
        // TODO: Implement business rules for viewing this OwnerDisbursement record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating OwnerDisbursement records.
        return false;
    }

    public function update(User $user, OwnerDisbursement $model): bool
    {
        // TODO: Implement business rules for updating this OwnerDisbursement record.
        return false;
    }

    public function delete(User $user, OwnerDisbursement $model): bool
    {
        // TODO: Implement business rules for deleting this OwnerDisbursement record.
        return false;
    }

    public function restore(User $user, OwnerDisbursement $model): bool
    {
        // TODO: Implement business rules for restoring this OwnerDisbursement record.
        return false;
    }

    public function forceDelete(User $user, OwnerDisbursement $model): bool
    {
        // TODO: Implement business rules for permanently deleting this OwnerDisbursement record.
        return false;
    }
}
