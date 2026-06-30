<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CustomFieldValue;

class CustomFieldValuePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing CustomFieldValue records.
        return false;
    }

    public function view(User $user, CustomFieldValue $model): bool
    {
        // TODO: Implement business rules for viewing this CustomFieldValue record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating CustomFieldValue records.
        return false;
    }

    public function update(User $user, CustomFieldValue $model): bool
    {
        // TODO: Implement business rules for updating this CustomFieldValue record.
        return false;
    }

    public function delete(User $user, CustomFieldValue $model): bool
    {
        // TODO: Implement business rules for deleting this CustomFieldValue record.
        return false;
    }

    public function restore(User $user, CustomFieldValue $model): bool
    {
        // TODO: Implement business rules for restoring this CustomFieldValue record.
        return false;
    }

    public function forceDelete(User $user, CustomFieldValue $model): bool
    {
        // TODO: Implement business rules for permanently deleting this CustomFieldValue record.
        return false;
    }
}
