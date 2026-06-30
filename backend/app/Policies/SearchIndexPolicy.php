<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SearchIndex;

class SearchIndexPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing SearchIndex records.
        return false;
    }

    public function view(User $user, SearchIndex $model): bool
    {
        // TODO: Implement business rules for viewing this SearchIndex record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating SearchIndex records.
        return false;
    }

    public function update(User $user, SearchIndex $model): bool
    {
        // TODO: Implement business rules for updating this SearchIndex record.
        return false;
    }

    public function delete(User $user, SearchIndex $model): bool
    {
        // TODO: Implement business rules for deleting this SearchIndex record.
        return false;
    }

    public function restore(User $user, SearchIndex $model): bool
    {
        // TODO: Implement business rules for restoring this SearchIndex record.
        return false;
    }

    public function forceDelete(User $user, SearchIndex $model): bool
    {
        // TODO: Implement business rules for permanently deleting this SearchIndex record.
        return false;
    }
}
