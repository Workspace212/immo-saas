<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('contracts.viewAny');
    }

    public function view(User $user, Contract $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('contracts.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessContract($user, $model);
        }

        if ($user->hasAnyRole(['owner', 'client'])) {
            // TODO: owners/clients tables have no user_id; cannot safely map portal users to contract parties yet.
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
            && $user->can('contracts.create');
    }

    public function update(User $user, Contract $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('contracts.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->agentCanAccessContract($user, $model);
    }

    public function delete(User $user, Contract $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('contracts.delete');
    }

    public function archive(User $user, Contract $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('contracts.archive');
    }

    public function cancel(User $user, Contract $model): bool
    {
        // TODO: permission catalog has no contracts.cancel permission; use archive endpoint/policy until split.
        return $this->archive($user, $model);
    }

    public function renew(User $user, Contract $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return $user->can('contracts.create') && $user->can('contracts.view');
        }

        return $user->hasRole('agent')
            && $user->can('contracts.create')
            && $user->can('contracts.view')
            && $this->agentCanAccessContract($user, $model);
    }

    public function restore(User $user, Contract $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('manager')) {
            return $user->can('contracts.restore');
        }

        return $user->hasRole('employee') && $user->can('contracts.restore');
    }

    public function forceDelete(User $user, Contract $model): bool
    {
        return false;
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

    private function sameAgency(User $user, Contract $contract): bool
    {
        return $user->agency_id !== null
            && $contract->agency_id !== null
            && (int) $user->agency_id === (int) $contract->agency_id;
    }

    private function agentCanAccessContract(User $user, Contract $contract): bool
    {
        $userId = (int) $user->getKey();
        $contractId = (int) $contract->getKey();
        $propertyId = $contract->property_id === null ? null : (int) $contract->property_id;

        if ((int) $contract->assigned_agent_id === $userId || (int) $contract->created_by === $userId) {
            return true;
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
                ->where('contract_id', $contractId)
                ->where('assigned_agent_id', $userId)
                ->exists()
            || DB::table('complaints')
                ->where('property_id', $propertyId)
                ->where('assigned_to', $userId)
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
}
