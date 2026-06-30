<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AgencySetting;

class AgencySettingPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing AgencySetting records.
        return false;
    }

    public function view(User $user, AgencySetting $model): bool
    {
        // TODO: Implement business rules for viewing this AgencySetting record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating AgencySetting records.
        return false;
    }

    public function update(User $user, AgencySetting $model): bool
    {
        // TODO: Implement business rules for updating this AgencySetting record.
        return false;
    }

    public function delete(User $user, AgencySetting $model): bool
    {
        // TODO: Implement business rules for deleting this AgencySetting record.
        return false;
    }

    public function restore(User $user, AgencySetting $model): bool
    {
        // TODO: Implement business rules for restoring this AgencySetting record.
        return false;
    }

    public function forceDelete(User $user, AgencySetting $model): bool
    {
        // TODO: Implement business rules for permanently deleting this AgencySetting record.
        return false;
    }
}
