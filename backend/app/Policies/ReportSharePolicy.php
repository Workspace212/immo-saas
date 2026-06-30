<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReportShare;

class ReportSharePolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ReportShare records.
        return false;
    }

    public function view(User $user, ReportShare $model): bool
    {
        // TODO: Implement business rules for viewing this ReportShare record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ReportShare records.
        return false;
    }

    public function update(User $user, ReportShare $model): bool
    {
        // TODO: Implement business rules for updating this ReportShare record.
        return false;
    }

    public function delete(User $user, ReportShare $model): bool
    {
        // TODO: Implement business rules for deleting this ReportShare record.
        return false;
    }

    public function restore(User $user, ReportShare $model): bool
    {
        // TODO: Implement business rules for restoring this ReportShare record.
        return false;
    }

    public function forceDelete(User $user, ReportShare $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ReportShare record.
        return false;
    }
}
