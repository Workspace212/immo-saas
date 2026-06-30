<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AppointmentParticipant;

class AppointmentParticipantPolicy
{
    public function viewAny(User $user): bool
    {
        // TODO: Implement business rules for listing AppointmentParticipant records.
        return false;
    }

    public function view(User $user, AppointmentParticipant $model): bool
    {
        // TODO: Implement business rules for viewing this AppointmentParticipant record.
        return false;
    }

    public function create(User $user): bool
    {
        // TODO: Implement business rules for creating AppointmentParticipant records.
        return false;
    }

    public function update(User $user, AppointmentParticipant $model): bool
    {
        // TODO: Implement business rules for updating this AppointmentParticipant record.
        return false;
    }

    public function delete(User $user, AppointmentParticipant $model): bool
    {
        // TODO: Implement business rules for deleting this AppointmentParticipant record.
        return false;
    }

    public function restore(User $user, AppointmentParticipant $model): bool
    {
        // TODO: Implement business rules for restoring this AppointmentParticipant record.
        return false;
    }

    public function forceDelete(User $user, AppointmentParticipant $model): bool
    {
        // TODO: Implement business rules for permanently deleting this AppointmentParticipant record.
        return false;
    }
}
