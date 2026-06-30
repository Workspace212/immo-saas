<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyInspectionPhoto;

class PropertyInspectionPhotoPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyInspectionPhoto records.
        return false;
    }

    public function view(User $user, PropertyInspectionPhoto $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyInspectionPhoto record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyInspectionPhoto records.
        return false;
    }

    public function update(User $user, PropertyInspectionPhoto $model): bool
    {
        // TODO: Implement business rules for updating this PropertyInspectionPhoto record.
        return false;
    }

    public function delete(User $user, PropertyInspectionPhoto $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyInspectionPhoto record.
        return false;
    }

    public function restore(User $user, PropertyInspectionPhoto $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyInspectionPhoto record.
        return false;
    }

    public function forceDelete(User $user, PropertyInspectionPhoto $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyInspectionPhoto record.
        return false;
    }
}
