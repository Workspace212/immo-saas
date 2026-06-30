<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BankReconciliation;

class BankReconciliationPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing BankReconciliation records.
        return false;
    }

    public function view(User $user, BankReconciliation $model): bool
    {
        // TODO: Implement business rules for viewing this BankReconciliation record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating BankReconciliation records.
        return false;
    }

    public function update(User $user, BankReconciliation $model): bool
    {
        // TODO: Implement business rules for updating this BankReconciliation record.
        return false;
    }

    public function delete(User $user, BankReconciliation $model): bool
    {
        // TODO: Implement business rules for deleting this BankReconciliation record.
        return false;
    }

    public function restore(User $user, BankReconciliation $model): bool
    {
        // TODO: Implement business rules for restoring this BankReconciliation record.
        return false;
    }

    public function forceDelete(User $user, BankReconciliation $model): bool
    {
        // TODO: Implement business rules for permanently deleting this BankReconciliation record.
        return false;
    }
}
