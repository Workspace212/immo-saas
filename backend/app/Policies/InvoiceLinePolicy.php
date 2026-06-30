<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InvoiceLine;

class InvoiceLinePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing InvoiceLine records.
        return false;
    }

    public function view(User $user, InvoiceLine $model): bool
    {
        // TODO: Implement business rules for viewing this InvoiceLine record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating InvoiceLine records.
        return false;
    }

    public function update(User $user, InvoiceLine $model): bool
    {
        // TODO: Implement business rules for updating this InvoiceLine record.
        return false;
    }

    public function delete(User $user, InvoiceLine $model): bool
    {
        // TODO: Implement business rules for deleting this InvoiceLine record.
        return false;
    }

    public function restore(User $user, InvoiceLine $model): bool
    {
        // TODO: Implement business rules for restoring this InvoiceLine record.
        return false;
    }

    public function forceDelete(User $user, InvoiceLine $model): bool
    {
        // TODO: Implement business rules for permanently deleting this InvoiceLine record.
        return false;
    }
}
