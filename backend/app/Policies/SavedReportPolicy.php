<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SavedReport;

class SavedReportPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing SavedReport records.
        return false;
    }

    public function view(User $user, SavedReport $model): bool
    {
        // TODO: Implement business rules for viewing this SavedReport record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating SavedReport records.
        return false;
    }

    public function update(User $user, SavedReport $model): bool
    {
        // TODO: Implement business rules for updating this SavedReport record.
        return false;
    }

    public function delete(User $user, SavedReport $model): bool
    {
        // TODO: Implement business rules for deleting this SavedReport record.
        return false;
    }

    public function restore(User $user, SavedReport $model): bool
    {
        // TODO: Implement business rules for restoring this SavedReport record.
        return false;
    }

    public function forceDelete(User $user, SavedReport $model): bool
    {
        // TODO: Implement business rules for permanently deleting this SavedReport record.
        return false;
    }
}
