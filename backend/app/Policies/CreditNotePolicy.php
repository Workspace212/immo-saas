<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CreditNote;

class CreditNotePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CreditNote records.
        return false;
    }

    public function view(User $user, CreditNote $model): bool
    {
        // TODO: Implement business rules for viewing this CreditNote record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CreditNote records.
        return false;
    }

    public function update(User $user, CreditNote $model): bool
    {
        // TODO: Implement business rules for updating this CreditNote record.
        return false;
    }

    public function delete(User $user, CreditNote $model): bool
    {
        // TODO: Implement business rules for deleting this CreditNote record.
        return false;
    }

    public function restore(User $user, CreditNote $model): bool
    {
        // TODO: Implement business rules for restoring this CreditNote record.
        return false;
    }

    public function forceDelete(User $user, CreditNote $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CreditNote record.
        return false;
    }
}
