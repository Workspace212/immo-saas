<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CommissionPayment;

class CommissionPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CommissionPayment records.
        return false;
    }

    public function view(User $user, CommissionPayment $model): bool
    {
        // TODO: Implement business rules for viewing this CommissionPayment record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CommissionPayment records.
        return false;
    }

    public function update(User $user, CommissionPayment $model): bool
    {
        // TODO: Implement business rules for updating this CommissionPayment record.
        return false;
    }

    public function delete(User $user, CommissionPayment $model): bool
    {
        // TODO: Implement business rules for deleting this CommissionPayment record.
        return false;
    }

    public function restore(User $user, CommissionPayment $model): bool
    {
        // TODO: Implement business rules for restoring this CommissionPayment record.
        return false;
    }

    public function forceDelete(User $user, CommissionPayment $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CommissionPayment record.
        return false;
    }
}
