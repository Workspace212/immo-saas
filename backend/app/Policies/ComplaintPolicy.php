<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('complaints.viewAny');
    }

    public function view(User $user, Complaint $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('complaints.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessComplaint($user, $model);
        }

        if ($user->hasRole('client')) {
            // TODO: clients table has no user_id; cannot safely match complaint.client_id to portal user.
            return false;
        }

        if ($user->hasRole('owner')) {
            // TODO: owners table has no user_id; cannot safely prove owned-property complaint access.
            return false;
        }

        if ($user->hasRole('provider')) {
            // TODO: providers table has no user_id; cannot safely prove complaint_providers assignment.
            return false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $user->hasRole('provider')) {
            return false;
        }

        if ($user->hasAnyRole(['client', 'owner'])) {
            // TODO: portal users cannot be safely tied to owned/client resources without user_id links.
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('complaints.create');
    }

    public function update(User $user, Complaint $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('complaints.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessComplaint($user, $model);
        }

        // TODO: external comment/status-only updates require dedicated endpoints and field-level authorization.
        return false;
    }

    public function delete(User $user, Complaint $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('complaints.delete');
    }

    public function assignToUser(User $user, Complaint $model): bool
    {
        return $this->manageWorkflow($user, $model);
    }

    public function markSeen(User $user, Complaint $model): bool
    {
        return $this->updateWorkflow($user, $model);
    }

    public function markInProgress(User $user, Complaint $model): bool
    {
        return $this->updateWorkflow($user, $model);
    }

    public function markWaitingProvider(User $user, Complaint $model): bool
    {
        return $this->updateWorkflow($user, $model);
    }

    public function resolve(User $user, Complaint $model): bool
    {
        return $this->manageWorkflow($user, $model);
    }

    public function close(User $user, Complaint $model): bool
    {
        return $this->manageWorkflow($user, $model);
    }

    public function reopen(User $user, Complaint $model): bool
    {
        return $this->manageWorkflow($user, $model);
    }

    public function archive(User $user, Complaint $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('complaints.archive');
    }

    public function restore(User $user, Complaint $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('complaints.restore');
    }

    public function forceDelete(User $user, Complaint $model): bool
    {
        return false;
    }

    private function updateWorkflow(User $user, Complaint $complaint): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $complaint)) {
            return false;
        }

        if (! $user->can('complaints.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->agentCanAccessComplaint($user, $complaint);
    }

    private function manageWorkflow(User $user, Complaint $complaint): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $complaint)) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return $user->can('complaints.manage') || $user->can('complaints.update');
        }

        return $user->hasRole('agent')
            && $user->can('complaints.update')
            && $this->agentCanAccessComplaint($user, $complaint);
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

    private function sameAgency(User $user, Complaint $complaint): bool
    {
        return $user->agency_id !== null
            && $complaint->agency_id !== null
            && (int) $user->agency_id === (int) $complaint->agency_id;
    }

    private function agentCanAccessComplaint(User $user, Complaint $complaint): bool
    {
        $userId = (int) $user->getKey();
        $complaintId = (int) $complaint->getKey();
        $propertyId = (int) $complaint->property_id;

        if ((int) $complaint->assigned_to === $userId || (int) $complaint->created_by === $userId) {
            return true;
        }

        return DB::table('properties')
            ->where('id', $propertyId)
            ->where(function ($query) use ($userId): void {
                $query->where('created_by', $userId)
                    ->orWhere('updated_by', $userId);
            })
            ->exists()
            || DB::table('contracts')
                ->where('property_id', $propertyId)
                ->where('assigned_agent_id', $userId)
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
                ->exists()
            || DB::table('appointments')
                ->where('complaint_id', $complaintId)
                ->where('created_by', $userId)
                ->exists();
    }
}
