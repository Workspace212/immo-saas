<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RentalUnit;

class RentalUnitPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing RentalUnit records.
        return false;
    }

    public function view(User $user, RentalUnit $model): bool
    {
        // TODO: Implement business rules for viewing this RentalUnit record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating RentalUnit records.
        return false;
    }

    public function update(User $user, RentalUnit $model): bool
    {
        // TODO: Implement business rules for updating this RentalUnit record.
        return false;
    }

    public function delete(User $user, RentalUnit $model): bool
    {
        // TODO: Implement business rules for deleting this RentalUnit record.
        return false;
    }

    public function restore(User $user, RentalUnit $model): bool
    {
        // TODO: Implement business rules for restoring this RentalUnit record.
        return false;
    }

    public function forceDelete(User $user, RentalUnit $model): bool
    {
        // TODO: Implement business rules for permanently deleting this RentalUnit record.
        return false;
    }
}
