<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BudgetLine;

class BudgetLinePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing BudgetLine records.
        return false;
    }

    public function view(User $user, BudgetLine $model): bool
    {
        // TODO: Implement business rules for viewing this BudgetLine record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating BudgetLine records.
        return false;
    }

    public function update(User $user, BudgetLine $model): bool
    {
        // TODO: Implement business rules for updating this BudgetLine record.
        return false;
    }

    public function delete(User $user, BudgetLine $model): bool
    {
        // TODO: Implement business rules for deleting this BudgetLine record.
        return false;
    }

    public function restore(User $user, BudgetLine $model): bool
    {
        // TODO: Implement business rules for restoring this BudgetLine record.
        return false;
    }

    public function forceDelete(User $user, BudgetLine $model): bool
    {
        // TODO: Implement business rules for permanently deleting this BudgetLine record.
        return false;
    }
}
