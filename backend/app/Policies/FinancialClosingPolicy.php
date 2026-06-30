<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinancialClosing;

class FinancialClosingPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing FinancialClosing records.
        return false;
    }

    public function view(User $user, FinancialClosing $model): bool
    {
        // TODO: Implement business rules for viewing this FinancialClosing record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating FinancialClosing records.
        return false;
    }

    public function update(User $user, FinancialClosing $model): bool
    {
        // TODO: Implement business rules for updating this FinancialClosing record.
        return false;
    }

    public function delete(User $user, FinancialClosing $model): bool
    {
        // TODO: Implement business rules for deleting this FinancialClosing record.
        return false;
    }

    public function restore(User $user, FinancialClosing $model): bool
    {
        // TODO: Implement business rules for restoring this FinancialClosing record.
        return false;
    }

    public function forceDelete(User $user, FinancialClosing $model): bool
    {
        // TODO: Implement business rules for permanently deleting this FinancialClosing record.
        return false;
    }
}
