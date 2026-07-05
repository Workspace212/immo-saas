<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('appointments.viewAny');
    }

    public function view(User $user, Appointment $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('appointments.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessAppointment($user, $model);
        }

        if ($user->hasAnyRole(['owner', 'client', 'provider'])) {
            // TODO: owner/client/provider records have no user_id; cannot safely match portal user to appointment participant.
            return false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('appointments.create');
    }

    public function update(User $user, Appointment $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('appointments.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->agentCanAccessAppointment($user, $model);
    }

    public function delete(User $user, Appointment $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('appointments.delete');
    }

    public function cancel(User $user, Appointment $model): bool
    {
        return $this->workflow($user, $model);
    }

    public function complete(User $user, Appointment $model): bool
    {
        return $this->workflow($user, $model);
    }

    public function markNoShow(User $user, Appointment $model): bool
    {
        return $this->workflow($user, $model);
    }

    public function archive(User $user, Appointment $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('appointments.archive');
    }

    public function restore(User $user, Appointment $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('appointments.restore');
    }

    public function forceDelete(User $user, Appointment $model): bool
    {
        return false;
    }

    private function workflow(User $user, Appointment $appointment): bool
    {
        // TODO: permission catalog has no appointment workflow permissions; using appointments.update.
        return $this->update($user, $appointment);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    private function hasInternalRole(User $user): bool
    {
        return $user->hasAnyRole(['manager', 'assistant', 'agent', 'employee']);
    }

    private function hasExternalRole(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'client', 'provider']);
    }

    private function hasAgency(User $user): bool
    {
        return $user->agency_id !== null;
    }

    private function sameAgency(User $user, Appointment $appointment): bool
    {
        return $user->agency_id !== null
            && $appointment->agency_id !== null
            && (int) $user->agency_id === (int) $appointment->agency_id;
    }

    private function agentCanAccessAppointment(User $user, Appointment $appointment): bool
    {
        $userId = (int) $user->getKey();
        $appointmentId = (int) $appointment->getKey();

        if ((int) $appointment->created_by === $userId) {
            return true;
        }

        if (DB::table('appointment_participants')
            ->where('appointment_id', $appointmentId)
            ->where('user_id', $userId)
            ->exists()) {
            return true;
        }

        if ($appointment->contract_id !== null && DB::table('contracts')
            ->where('id', (int) $appointment->contract_id)
            ->where('assigned_agent_id', $userId)
            ->exists()) {
            return true;
        }

        if ($appointment->complaint_id !== null && DB::table('complaints')
            ->where('id', (int) $appointment->complaint_id)
            ->where(function ($query) use ($userId): void {
                $query->where('assigned_to', $userId)
                    ->orWhere('created_by', $userId);
            })
            ->exists()) {
            return true;
        }

        if ($appointment->collaboration_id !== null && $this->agentCanAccessCollaboration((int) $appointment->collaboration_id, $userId)) {
            return true;
        }

        $propertyId = $appointment->property_id === null ? null : (int) $appointment->property_id;

        if ($propertyId === null && $appointment->contract_id !== null) {
            $propertyId = DB::table('contracts')
                ->where('id', (int) $appointment->contract_id)
                ->value('property_id');
            $propertyId = $propertyId === null ? null : (int) $propertyId;
        }

        if ($propertyId === null) {
            return false;
        }

        return DB::table('properties')
            ->where('id', $propertyId)
            ->where(function ($query) use ($userId): void {
                $query->where('created_by', $userId)
                    ->orWhere('updated_by', $userId);
            })
            ->exists()
            || DB::table('rental_units')
                ->where('property_id', $propertyId)
                ->where('assigned_agent_id', $userId)
                ->exists()
            || DB::table('collaborations')
                ->where('property_id', $propertyId)
                ->where(function ($query) use ($userId): void {
                    $query->where('requesting_agent_id', $userId)
                        ->orWhere('owner_agent_id', $userId)
                        ->orWhere('created_by', $userId);
                })
                ->exists();
    }

    private function agentCanAccessCollaboration(int $collaborationId, int $userId): bool
    {
        return DB::table('collaborations')
            ->where('id', $collaborationId)
            ->where(function ($query) use ($userId): void {
                $query->where('requesting_agent_id', $userId)
                    ->orWhere('owner_agent_id', $userId)
                    ->orWhere('created_by', $userId);
            })
            ->exists();
    }
}
