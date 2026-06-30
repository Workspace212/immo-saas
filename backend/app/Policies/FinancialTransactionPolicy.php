<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinancialTransaction;

class FinancialTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing FinancialTransaction records.
        return false;
    }

    public function view(User $user, FinancialTransaction $model): bool
    {
        // TODO: Implement business rules for viewing this FinancialTransaction record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating FinancialTransaction records.
        return false;
    }

    public function update(User $user, FinancialTransaction $model): bool
    {
        // TODO: Implement business rules for updating this FinancialTransaction record.
        return false;
    }

    public function delete(User $user, FinancialTransaction $model): bool
    {
        // TODO: Implement business rules for deleting this FinancialTransaction record.
        return false;
    }

    public function restore(User $user, FinancialTransaction $model): bool
    {
        // TODO: Implement business rules for restoring this FinancialTransaction record.
        return false;
    }

    public function forceDelete(User $user, FinancialTransaction $model): bool
    {
        // TODO: Implement business rules for permanently deleting this FinancialTransaction record.
        return false;
    }
}
