<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Invoice records.
        return false;
    }

    public function view(User $user, Invoice $model): bool
    {
        // TODO: Implement business rules for viewing this Invoice record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Invoice records.
        return false;
    }

    public function update(User $user, Invoice $model): bool
    {
        // TODO: Implement business rules for updating this Invoice record.
        return false;
    }

    public function delete(User $user, Invoice $model): bool
    {
        // TODO: Implement business rules for deleting this Invoice record.
        return false;
    }

    public function restore(User $user, Invoice $model): bool
    {
        // TODO: Implement business rules for restoring this Invoice record.
        return false;
    }

    public function forceDelete(User $user, Invoice $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Invoice record.
        return false;
    }
}
