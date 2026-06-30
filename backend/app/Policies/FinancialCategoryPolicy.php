<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinancialCategory;

class FinancialCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing FinancialCategory records.
        return false;
    }

    public function view(User $user, FinancialCategory $model): bool
    {
        // TODO: Implement business rules for viewing this FinancialCategory record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating FinancialCategory records.
        return false;
    }

    public function update(User $user, FinancialCategory $model): bool
    {
        // TODO: Implement business rules for updating this FinancialCategory record.
        return false;
    }

    public function delete(User $user, FinancialCategory $model): bool
    {
        // TODO: Implement business rules for deleting this FinancialCategory record.
        return false;
    }

    public function restore(User $user, FinancialCategory $model): bool
    {
        // TODO: Implement business rules for restoring this FinancialCategory record.
        return false;
    }

    public function forceDelete(User $user, FinancialCategory $model): bool
    {
        // TODO: Implement business rules for permanently deleting this FinancialCategory record.
        return false;
    }
}
