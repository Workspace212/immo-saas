<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SearchSuggestion;

class SearchSuggestionPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing SearchSuggestion records.
        return false;
    }

    public function view(User $user, SearchSuggestion $model): bool
    {
        // TODO: Implement business rules for viewing this SearchSuggestion record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating SearchSuggestion records.
        return false;
    }

    public function update(User $user, SearchSuggestion $model): bool
    {
        // TODO: Implement business rules for updating this SearchSuggestion record.
        return false;
    }

    public function delete(User $user, SearchSuggestion $model): bool
    {
        // TODO: Implement business rules for deleting this SearchSuggestion record.
        return false;
    }

    public function restore(User $user, SearchSuggestion $model): bool
    {
        // TODO: Implement business rules for restoring this SearchSuggestion record.
        return false;
    }

    public function forceDelete(User $user, SearchSuggestion $model): bool
    {
        // TODO: Implement business rules for permanently deleting this SearchSuggestion record.
        return false;
    }
}
