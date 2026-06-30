<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReportExport;

class ReportExportPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing ReportExport records.
        return false;
    }

    public function view(User $user, ReportExport $model): bool
    {
        // TODO: Implement business rules for viewing this ReportExport record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating ReportExport records.
        return false;
    }

    public function update(User $user, ReportExport $model): bool
    {
        // TODO: Implement business rules for updating this ReportExport record.
        return false;
    }

    public function delete(User $user, ReportExport $model): bool
    {
        // TODO: Implement business rules for deleting this ReportExport record.
        return false;
    }

    public function restore(User $user, ReportExport $model): bool
    {
        // TODO: Implement business rules for restoring this ReportExport record.
        return false;
    }

    public function forceDelete(User $user, ReportExport $model): bool
    {
        // TODO: Implement business rules for permanently deleting this ReportExport record.
        return false;
    }
}
