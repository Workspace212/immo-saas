<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CommissionRule;

class CommissionRulePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CommissionRule records.
        return false;
    }

    public function view(User $user, CommissionRule $model): bool
    {
        // TODO: Implement business rules for viewing this CommissionRule record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CommissionRule records.
        return false;
    }

    public function update(User $user, CommissionRule $model): bool
    {
        // TODO: Implement business rules for updating this CommissionRule record.
        return false;
    }

    public function delete(User $user, CommissionRule $model): bool
    {
        // TODO: Implement business rules for deleting this CommissionRule record.
        return false;
    }

    public function restore(User $user, CommissionRule $model): bool
    {
        // TODO: Implement business rules for restoring this CommissionRule record.
        return false;
    }

    public function forceDelete(User $user, CommissionRule $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CommissionRule record.
        return false;
    }
}
