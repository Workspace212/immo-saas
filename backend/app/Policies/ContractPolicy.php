<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contract;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Contract records.
        return false;
    }

    public function view(User $user, Contract $model): bool
    {
        // TODO: Implement business rules for viewing this Contract record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Contract records.
        return false;
    }

    public function update(User $user, Contract $model): bool
    {
        // TODO: Implement business rules for updating this Contract record.
        return false;
    }

    public function delete(User $user, Contract $model): bool
    {
        // TODO: Implement business rules for deleting this Contract record.
        return false;
    }

    public function restore(User $user, Contract $model): bool
    {
        // TODO: Implement business rules for restoring this Contract record.
        return false;
    }

    public function forceDelete(User $user, Contract $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Contract record.
        return false;
    }
}
