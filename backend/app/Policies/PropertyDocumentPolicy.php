<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PropertyDocument;

class PropertyDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing PropertyDocument records.
        return false;
    }

    public function view(User $user, PropertyDocument $model): bool
    {
        // TODO: Implement business rules for viewing this PropertyDocument record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating PropertyDocument records.
        return false;
    }

    public function update(User $user, PropertyDocument $model): bool
    {
        // TODO: Implement business rules for updating this PropertyDocument record.
        return false;
    }

    public function delete(User $user, PropertyDocument $model): bool
    {
        // TODO: Implement business rules for deleting this PropertyDocument record.
        return false;
    }

    public function restore(User $user, PropertyDocument $model): bool
    {
        // TODO: Implement business rules for restoring this PropertyDocument record.
        return false;
    }

    public function forceDelete(User $user, PropertyDocument $model): bool
    {
        // TODO: Implement business rules for permanently deleting this PropertyDocument record.
        return false;
    }
}
