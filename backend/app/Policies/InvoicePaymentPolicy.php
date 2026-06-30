<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InvoicePayment;

class InvoicePaymentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing InvoicePayment records.
        return false;
    }

    public function view(User $user, InvoicePayment $model): bool
    {
        // TODO: Implement business rules for viewing this InvoicePayment record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating InvoicePayment records.
        return false;
    }

    public function update(User $user, InvoicePayment $model): bool
    {
        // TODO: Implement business rules for updating this InvoicePayment record.
        return false;
    }

    public function delete(User $user, InvoicePayment $model): bool
    {
        // TODO: Implement business rules for deleting this InvoicePayment record.
        return false;
    }

    public function restore(User $user, InvoicePayment $model): bool
    {
        // TODO: Implement business rules for restoring this InvoicePayment record.
        return false;
    }

    public function forceDelete(User $user, InvoicePayment $model): bool
    {
        // TODO: Implement business rules for permanently deleting this InvoicePayment record.
        return false;
    }
}
