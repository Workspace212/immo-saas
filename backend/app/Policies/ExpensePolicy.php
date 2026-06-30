<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Expense records.
        return false;
    }

    public function view(User $user, Expense $model): bool
    {
        // TODO: Implement business rules for viewing this Expense record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Expense records.
        return false;
    }

    public function update(User $user, Expense $model): bool
    {
        // TODO: Implement business rules for updating this Expense record.
        return false;
    }

    public function delete(User $user, Expense $model): bool
    {
        // TODO: Implement business rules for deleting this Expense record.
        return false;
    }

    public function restore(User $user, Expense $model): bool
    {
        // TODO: Implement business rules for restoring this Expense record.
        return false;
    }

    public function forceDelete(User $user, Expense $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Expense record.
        return false;
    }
}
