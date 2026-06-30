<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinancialAccount;

class FinancialAccountPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing FinancialAccount records.
        return false;
    }

    public function view(User $user, FinancialAccount $model): bool
    {
        // TODO: Implement business rules for viewing this FinancialAccount record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating FinancialAccount records.
        return false;
    }

    public function update(User $user, FinancialAccount $model): bool
    {
        // TODO: Implement business rules for updating this FinancialAccount record.
        return false;
    }

    public function delete(User $user, FinancialAccount $model): bool
    {
        // TODO: Implement business rules for deleting this FinancialAccount record.
        return false;
    }

    public function restore(User $user, FinancialAccount $model): bool
    {
        // TODO: Implement business rules for restoring this FinancialAccount record.
        return false;
    }

    public function forceDelete(User $user, FinancialAccount $model): bool
    {
        // TODO: Implement business rules for permanently deleting this FinancialAccount record.
        return false;
    }
}
