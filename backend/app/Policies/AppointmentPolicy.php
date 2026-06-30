<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing Appointment records.
        return false;
    }

    public function view(User $user, Appointment $model): bool
    {
        // TODO: Implement business rules for viewing this Appointment record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating Appointment records.
        return false;
    }

    public function update(User $user, Appointment $model): bool
    {
        // TODO: Implement business rules for updating this Appointment record.
        return false;
    }

    public function delete(User $user, Appointment $model): bool
    {
        // TODO: Implement business rules for deleting this Appointment record.
        return false;
    }

    public function restore(User $user, Appointment $model): bool
    {
        // TODO: Implement business rules for restoring this Appointment record.
        return false;
    }

    public function forceDelete(User $user, Appointment $model): bool
    {
        // TODO: Implement business rules for permanently deleting this Appointment record.
        return false;
    }
}
