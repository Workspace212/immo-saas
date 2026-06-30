<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SavedSearch;

class SavedSearchPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing SavedSearch records.
        return false;
    }

    public function view(User $user, SavedSearch $model): bool
    {
        // TODO: Implement business rules for viewing this SavedSearch record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating SavedSearch records.
        return false;
    }

    public function update(User $user, SavedSearch $model): bool
    {
        // TODO: Implement business rules for updating this SavedSearch record.
        return false;
    }

    public function delete(User $user, SavedSearch $model): bool
    {
        // TODO: Implement business rules for deleting this SavedSearch record.
        return false;
    }

    public function restore(User $user, SavedSearch $model): bool
    {
        // TODO: Implement business rules for restoring this SavedSearch record.
        return false;
    }

    public function forceDelete(User $user, SavedSearch $model): bool
    {
        // TODO: Implement business rules for permanently deleting this SavedSearch record.
        return false;
    }
}
