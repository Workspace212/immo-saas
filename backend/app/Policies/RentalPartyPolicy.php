<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RentalParty;

class RentalPartyPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing RentalParty records.
        return false;
    }

    public function view(User $user, RentalParty $model): bool
    {
        // TODO: Implement business rules for viewing this RentalParty record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating RentalParty records.
        return false;
    }

    public function update(User $user, RentalParty $model): bool
    {
        // TODO: Implement business rules for updating this RentalParty record.
        return false;
    }

    public function delete(User $user, RentalParty $model): bool
    {
        // TODO: Implement business rules for deleting this RentalParty record.
        return false;
    }

    public function restore(User $user, RentalParty $model): bool
    {
        // TODO: Implement business rules for restoring this RentalParty record.
        return false;
    }

    public function forceDelete(User $user, RentalParty $model): bool
    {
        // TODO: Implement business rules for permanently deleting this RentalParty record.
        return false;
    }
}
