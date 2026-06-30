<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContractType;

class ContractTypePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ContractType records.
        return false;
    }

    public function view(User $user, ContractType $model): bool
    {
        // TODO: Implement business rules for viewing this ContractType record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ContractType records.
        return false;
    }

    public function update(User $user, ContractType $model): bool
    {
        // TODO: Implement business rules for updating this ContractType record.
        return false;
    }

    public function delete(User $user, ContractType $model): bool
    {
        // TODO: Implement business rules for deleting this ContractType record.
        return false;
    }

    public function restore(User $user, ContractType $model): bool
    {
        // TODO: Implement business rules for restoring this ContractType record.
        return false;
    }

    public function forceDelete(User $user, ContractType $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ContractType record.
        return false;
    }
}
