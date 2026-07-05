<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('properties.viewAny');
    }

    public function view(User $user, Property $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('properties.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'agent', 'employee'])) {
            return true;
        }

        if ($user->hasRole('owner')) {
            // TODO: owners table has no user_id; cannot safely map User to Owner record yet.
            return false;
        }

        if ($user->hasRole('client')) {
            // TODO: clients table has no user_id; cannot safely map User to Client record yet.
            return false;
        }

        if ($user->hasRole('provider')) {
            // TODO: providers table has no user_id; cannot safely map User to Provider record yet.
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
            && $user->can('properties.create');
    }

    public function update(User $user, Property $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('properties.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->isResponsibleForProperty($user, $model);
    }

    public function delete(User $user, Property $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        if ($user->hasRole('manager')) {
            return $user->can('properties.delete');
        }

        return $user->hasAnyRole(['assistant', 'employee']) && $user->can('properties.delete');
    }

    public function archive(User $user, Property $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('properties.archive')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->isResponsibleForProperty($user, $model);
    }

    public function restore(User $user, Property $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('properties.restore');
    }

    public function forceDelete(User $user, Property $model): bool
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

    private function sameAgency(User $user, Property $property): bool
    {
        return $user->agency_id !== null
            && $property->agency_id !== null
            && (int) $user->agency_id === (int) $property->agency_id;
    }

    private function isResponsibleForProperty(User $user, Property $property): bool
    {
        $userId = (int) $user->getKey();
        $propertyId = (int) $property->getKey();

        if ((int) $property->created_by === $userId || (int) $property->updated_by === $userId) {
            return true;
        }

        return DB::table('contracts')
            ->where('property_id', $propertyId)
            ->where('assigned_agent_id', $userId)
            ->exists()
            || DB::table('rental_units')
                ->where('property_id', $propertyId)
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
