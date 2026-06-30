<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Report records.
        return false;
    }

    public function view(User $user, Report $model): bool
    {
        // TODO: Implement business rules for viewing this Report record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Report records.
        return false;
    }

    public function update(User $user, Report $model): bool
    {
        // TODO: Implement business rules for updating this Report record.
        return false;
    }

    public function delete(User $user, Report $model): bool
    {
        // TODO: Implement business rules for deleting this Report record.
        return false;
    }

    public function restore(User $user, Report $model): bool
    {
        // TODO: Implement business rules for restoring this Report record.
        return false;
    }

    public function forceDelete(User $user, Report $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Report record.
        return false;
    }
}
