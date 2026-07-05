<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OwnerPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('owners.viewAny');
    }

    public function view(User $user, Owner $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('owners.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessOwner($user, $model);
        }

        if ($user->hasRole('owner')) {
            // TODO: owners table has no user_id; cannot safely authorize own Owner record yet.
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
            && $user->can('owners.create');
    }

    public function update(User $user, Owner $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('owners.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessOwner($user, $model);
        }

        if ($user->hasRole('owner')) {
            // TODO: owners table has no user_id, and field-level restrictions for legal/internal fields are not enforced yet.
            return false;
        }

        return false;
    }

    public function delete(User $user, Owner $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('owners.delete');
    }

    public function archive(User $user, Owner $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('owners.archive');
    }

    public function restore(User $user, Owner $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('owners.restore');
    }

    public function forceDelete(User $user, Owner $model): bool
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

    private function sameAgency(User $user, Owner $owner): bool
    {
        return $user->agency_id !== null
            && $owner->agency_id !== null
            && (int) $user->agency_id === (int) $owner->agency_id;
    }

    private function agentCanAccessOwner(User $user, Owner $owner): bool
    {
        $userId = (int) $user->getKey();
        $ownerId = (int) $owner->getKey();

        return DB::table('property_owners')
            ->join('properties', 'properties.id', '=', 'property_owners.property_id')
            ->where('property_owners.owner_id', $ownerId)
            ->where(function ($query) use ($userId): void {
                $query->where('properties.created_by', $userId)
                    ->orWhere('properties.updated_by', $userId)
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->whereColumn('contracts.property_id', 'properties.id')
                            ->where('contracts.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_units')
                            ->whereColumn('rental_units.property_id', 'properties.id')
                            ->where('rental_units.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('complaints')
                            ->whereColumn('complaints.property_id', 'properties.id')
                            ->where('complaints.assigned_to', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('collaborations')
                            ->whereColumn('collaborations.property_id', 'properties.id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    });
            })
            ->exists();
    }
}
