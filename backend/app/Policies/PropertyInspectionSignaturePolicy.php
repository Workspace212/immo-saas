<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyInspectionSignature;

class PropertyInspectionSignaturePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyInspectionSignature records.
        return false;
    }

    public function view(User $user, PropertyInspectionSignature $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyInspectionSignature record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyInspectionSignature records.
        return false;
    }

    public function update(User $user, PropertyInspectionSignature $model): bool
    {
        // TODO: Implement business rules for updating this PropertyInspectionSignature record.
        return false;
    }

    public function delete(User $user, PropertyInspectionSignature $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyInspectionSignature record.
        return false;
    }

    public function restore(User $user, PropertyInspectionSignature $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyInspectionSignature record.
        return false;
    }

    public function forceDelete(User $user, PropertyInspectionSignature $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyInspectionSignature record.
        return false;
    }
}
