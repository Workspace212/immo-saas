<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ScheduledReport;

class ScheduledReportPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ScheduledReport records.
        return false;
    }

    public function view(User $user, ScheduledReport $model): bool
    {
        // TODO: Implement business rules for viewing this ScheduledReport record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ScheduledReport records.
        return false;
    }

    public function update(User $user, ScheduledReport $model): bool
    {
        // TODO: Implement business rules for updating this ScheduledReport record.
        return false;
    }

    public function delete(User $user, ScheduledReport $model): bool
    {
        // TODO: Implement business rules for deleting this ScheduledReport record.
        return false;
    }

    public function restore(User $user, ScheduledReport $model): bool
    {
        // TODO: Implement business rules for restoring this ScheduledReport record.
        return false;
    }

    public function forceDelete(User $user, ScheduledReport $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ScheduledReport record.
        return false;
    }
}
