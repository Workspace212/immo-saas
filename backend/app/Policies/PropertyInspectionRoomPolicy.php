<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyInspectionRoom;

class PropertyInspectionRoomPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyInspectionRoom records.
        return false;
    }

    public function view(User $user, PropertyInspectionRoom $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyInspectionRoom record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyInspectionRoom records.
        return false;
    }

    public function update(User $user, PropertyInspectionRoom $model): bool
    {
        // TODO: Implement business rules for updating this PropertyInspectionRoom record.
        return false;
    }

    public function delete(User $user, PropertyInspectionRoom $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyInspectionRoom record.
        return false;
    }

    public function restore(User $user, PropertyInspectionRoom $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyInspectionRoom record.
        return false;
    }

    public function forceDelete(User $user, PropertyInspectionRoom $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyInspectionRoom record.
        return false;
    }
}
