<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CustomField;

class CustomFieldPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CustomField records.
        return false;
    }

    public function view(User $user, CustomField $model): bool
    {
        // TODO: Implement business rules for viewing this CustomField record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CustomField records.
        return false;
    }

    public function update(User $user, CustomField $model): bool
    {
        // TODO: Implement business rules for updating this CustomField record.
        return false;
    }

    public function delete(User $user, CustomField $model): bool
    {
        // TODO: Implement business rules for deleting this CustomField record.
        return false;
    }

    public function restore(User $user, CustomField $model): bool
    {
        // TODO: Implement business rules for restoring this CustomField record.
        return false;
    }

    public function forceDelete(User $user, CustomField $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CustomField record.
        return false;
    }
}
