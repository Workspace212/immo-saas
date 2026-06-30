<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContractDocument;

class ContractDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ContractDocument records.
        return false;
    }

    public function view(User $user, ContractDocument $model): bool
    {
        // TODO: Implement business rules for viewing this ContractDocument record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ContractDocument records.
        return false;
    }

    public function update(User $user, ContractDocument $model): bool
    {
        // TODO: Implement business rules for updating this ContractDocument record.
        return false;
    }

    public function delete(User $user, ContractDocument $model): bool
    {
        // TODO: Implement business rules for deleting this ContractDocument record.
        return false;
    }

    public function restore(User $user, ContractDocument $model): bool
    {
        // TODO: Implement business rules for restoring this ContractDocument record.
        return false;
    }

    public function forceDelete(User $user, ContractDocument $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ContractDocument record.
        return false;
    }
}
