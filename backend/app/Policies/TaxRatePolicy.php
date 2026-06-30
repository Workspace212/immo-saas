<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TaxRate;

class TaxRatePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing TaxRate records.
        return false;
    }

    public function view(User $user, TaxRate $model): bool
    {
        // TODO: Implement business rules for viewing this TaxRate record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating TaxRate records.
        return false;
    }

    public function update(User $user, TaxRate $model): bool
    {
        // TODO: Implement business rules for updating this TaxRate record.
        return false;
    }

    public function delete(User $user, TaxRate $model): bool
    {
        // TODO: Implement business rules for deleting this TaxRate record.
        return false;
    }

    public function restore(User $user, TaxRate $model): bool
    {
        // TODO: Implement business rules for restoring this TaxRate record.
        return false;
    }

    public function forceDelete(User $user, TaxRate $model): bool
    {
        // TODO: Implement business rules for permanently deleting this TaxRate record.
        return false;
    }
}
