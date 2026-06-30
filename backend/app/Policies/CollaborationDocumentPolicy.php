<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CollaborationDocument;

class CollaborationDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CollaborationDocument records.
        return false;
    }

    public function view(User $user, CollaborationDocument $model): bool
    {
        // TODO: Implement business rules for viewing this CollaborationDocument record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CollaborationDocument records.
        return false;
    }

    public function update(User $user, CollaborationDocument $model): bool
    {
        // TODO: Implement business rules for updating this CollaborationDocument record.
        return false;
    }

    public function delete(User $user, CollaborationDocument $model): bool
    {
        // TODO: Implement business rules for deleting this CollaborationDocument record.
        return false;
    }

    public function restore(User $user, CollaborationDocument $model): bool
    {
        // TODO: Implement business rules for restoring this CollaborationDocument record.
        return false;
    }

    public function forceDelete(User $user, CollaborationDocument $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CollaborationDocument record.
        return false;
    }
}
