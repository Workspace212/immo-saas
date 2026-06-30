<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinancialAttachment;

class FinancialAttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing FinancialAttachment records.
        return false;
    }

    public function view(User $user, FinancialAttachment $model): bool
    {
        // TODO: Implement business rules for viewing this FinancialAttachment record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating FinancialAttachment records.
        return false;
    }

    public function update(User $user, FinancialAttachment $model): bool
    {
        // TODO: Implement business rules for updating this FinancialAttachment record.
        return false;
    }

    public function delete(User $user, FinancialAttachment $model): bool
    {
        // TODO: Implement business rules for deleting this FinancialAttachment record.
        return false;
    }

    public function restore(User $user, FinancialAttachment $model): bool
    {
        // TODO: Implement business rules for restoring this FinancialAttachment record.
        return false;
    }

    public function forceDelete(User $user, FinancialAttachment $model): bool
    {
        // TODO: Implement business rules for permanently deleting this FinancialAttachment record.
        return false;
    }
}
