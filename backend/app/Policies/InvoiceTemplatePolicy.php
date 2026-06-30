<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InvoiceTemplate;

class InvoiceTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing InvoiceTemplate records.
        return false;
    }

    public function view(User $user, InvoiceTemplate $model): bool
    {
        // TODO: Implement business rules for viewing this InvoiceTemplate record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating InvoiceTemplate records.
        return false;
    }

    public function update(User $user, InvoiceTemplate $model): bool
    {
        // TODO: Implement business rules for updating this InvoiceTemplate record.
        return false;
    }

    public function delete(User $user, InvoiceTemplate $model): bool
    {
        // TODO: Implement business rules for deleting this InvoiceTemplate record.
        return false;
    }

    public function restore(User $user, InvoiceTemplate $model): bool
    {
        // TODO: Implement business rules for restoring this InvoiceTemplate record.
        return false;
    }

    public function forceDelete(User $user, InvoiceTemplate $model): bool
    {
        // TODO: Implement business rules for permanently deleting this InvoiceTemplate record.
        return false;
    }
}
