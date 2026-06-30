<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyInspection;

class PropertyInspectionPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyInspection records.
        return false;
    }

    public function view(User $user, PropertyInspection $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyInspection record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyInspection records.
        return false;
    }

    public function update(User $user, PropertyInspection $model): bool
    {
        // TODO: Implement business rules for updating this PropertyInspection record.
        return false;
    }

    public function delete(User $user, PropertyInspection $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyInspection record.
        return false;
    }

    public function restore(User $user, PropertyInspection $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyInspection record.
        return false;
    }

    public function forceDelete(User $user, PropertyInspection $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyInspection record.
        return false;
    }
}
