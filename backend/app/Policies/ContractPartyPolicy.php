<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContractParty;

class ContractPartyPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ContractParty records.
        return false;
    }

    public function view(User $user, ContractParty $model): bool
    {
        // TODO: Implement business rules for viewing this ContractParty record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ContractParty records.
        return false;
    }

    public function update(User $user, ContractParty $model): bool
    {
        // TODO: Implement business rules for updating this ContractParty record.
        return false;
    }

    public function delete(User $user, ContractParty $model): bool
    {
        // TODO: Implement business rules for deleting this ContractParty record.
        return false;
    }

    public function restore(User $user, ContractParty $model): bool
    {
        // TODO: Implement business rules for restoring this ContractParty record.
        return false;
    }

    public function forceDelete(User $user, ContractParty $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ContractParty record.
        return false;
    }
}
