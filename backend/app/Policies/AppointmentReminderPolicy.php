<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AppointmentReminder;

class AppointmentReminderPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing AppointmentReminder records.
        return false;
    }

    public function view(User $user, AppointmentReminder $model): bool
    {
        // TODO: Implement business rules for viewing this AppointmentReminder record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating AppointmentReminder records.
        return false;
    }

    public function update(User $user, AppointmentReminder $model): bool
    {
        // TODO: Implement business rules for updating this AppointmentReminder record.
        return false;
    }

    public function delete(User $user, AppointmentReminder $model): bool
    {
        // TODO: Implement business rules for deleting this AppointmentReminder record.
        return false;
    }

    public function restore(User $user, AppointmentReminder $model): bool
    {
        // TODO: Implement business rules for restoring this AppointmentReminder record.
        return false;
    }

    public function forceDelete(User $user, AppointmentReminder $model): bool
    {
        // TODO: Implement business rules for permanently deleting this AppointmentReminder record.
        return false;
    }
}
