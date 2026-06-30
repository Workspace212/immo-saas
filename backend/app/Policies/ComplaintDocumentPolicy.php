<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ComplaintDocument;

class ComplaintDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ComplaintDocument records.
        return false;
    }

    public function view(User $user, ComplaintDocument $model): bool
    {
        // TODO: Implement business rules for viewing this ComplaintDocument record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ComplaintDocument records.
        return false;
    }

    public function update(User $user, ComplaintDocument $model): bool
    {
        // TODO: Implement business rules for updating this ComplaintDocument record.
        return false;
    }

    public function delete(User $user, ComplaintDocument $model): bool
    {
        // TODO: Implement business rules for deleting this ComplaintDocument record.
        return false;
    }

    public function restore(User $user, ComplaintDocument $model): bool
    {
        // TODO: Implement business rules for restoring this ComplaintDocument record.
        return false;
    }

    public function forceDelete(User $user, ComplaintDocument $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ComplaintDocument record.
        return false;
    }
}
