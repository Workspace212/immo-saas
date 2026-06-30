<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinancialDocument;

class FinancialDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing FinancialDocument records.
        return false;
    }

    public function view(User $user, FinancialDocument $model): bool
    {
        // TODO: Implement business rules for viewing this FinancialDocument record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating FinancialDocument records.
        return false;
    }

    public function update(User $user, FinancialDocument $model): bool
    {
        // TODO: Implement business rules for updating this FinancialDocument record.
        return false;
    }

    public function delete(User $user, FinancialDocument $model): bool
    {
        // TODO: Implement business rules for deleting this FinancialDocument record.
        return false;
    }

    public function restore(User $user, FinancialDocument $model): bool
    {
        // TODO: Implement business rules for restoring this FinancialDocument record.
        return false;
    }

    public function forceDelete(User $user, FinancialDocument $model): bool
    {
        // TODO: Implement business rules for permanently deleting this FinancialDocument record.
        return false;
    }
}
