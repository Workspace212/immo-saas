<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContractPayment;

class ContractPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ContractPayment records.
        return false;
    }

    public function view(User $user, ContractPayment $model): bool
    {
        // TODO: Implement business rules for viewing this ContractPayment record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ContractPayment records.
        return false;
    }

    public function update(User $user, ContractPayment $model): bool
    {
        // TODO: Implement business rules for updating this ContractPayment record.
        return false;
    }

    public function delete(User $user, ContractPayment $model): bool
    {
        // TODO: Implement business rules for deleting this ContractPayment record.
        return false;
    }

    public function restore(User $user, ContractPayment $model): bool
    {
        // TODO: Implement business rules for restoring this ContractPayment record.
        return false;
    }

    public function forceDelete(User $user, ContractPayment $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ContractPayment record.
        return false;
    }
}
