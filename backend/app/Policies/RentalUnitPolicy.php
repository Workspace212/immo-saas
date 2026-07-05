<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RentalUnitPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('rentals.viewAny');
    }

    public function view(User $user, RentalUnit $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('rentals.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessRental($user, $model);
        }

        if ($user->hasAnyRole(['owner', 'client'])) {
            // TODO: owners/clients tables have no user_id; cannot safely map portal users to rental relations yet.
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
            && $user->can('rentals.create');
    }

    public function update(User $user, RentalUnit $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('rentals.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        return $user->hasRole('agent') && $this->agentCanAccessRental($user, $model);
    }

    public function delete(User $user, RentalUnit $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('rentals.delete');
    }

    public function activate(User $user, RentalUnit $model): bool
    {
        // TODO: permission catalog has no rentals.activate permission; using rentals.update for lifecycle activation.
        return $this->update($user, $model);
    }

    public function end(User $user, RentalUnit $model): bool
    {
        // TODO: permission catalog has no rentals.end permission; using rentals.archive for destructive lifecycle closure.
        return $this->destructiveLifecycle($user, $model);
    }

    public function cancel(User $user, RentalUnit $model): bool
    {
        // TODO: permission catalog has no rentals.cancel permission; using rentals.archive for destructive lifecycle cancellation.
        return $this->destructiveLifecycle($user, $model);
    }

    public function renew(User $user, RentalUnit $model): bool
    {
        // TODO: permission catalog has no rentals.renew permission; using rentals.create + rentals.view for renewal.
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return $user->can('rentals.create') && $user->can('rentals.view');
        }

        return $user->hasRole('agent')
            && $user->can('rentals.create')
            && $user->can('rentals.view')
            && $this->agentCanAccessRental($user, $model);
    }

    public function archive(User $user, RentalUnit $model): bool
    {
        return $this->destructiveLifecycle($user, $model);
    }

    public function restore(User $user, RentalUnit $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('rentals.restore');
    }

    public function forceDelete(User $user, RentalUnit $model): bool
    {
        return false;
    }

    private function destructiveLifecycle(User $user, RentalUnit $rentalUnit): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $rentalUnit)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('rentals.archive');
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

    private function sameAgency(User $user, RentalUnit $rentalUnit): bool
    {
        return $user->agency_id !== null
            && $rentalUnit->agency_id !== null
            && (int) $user->agency_id === (int) $rentalUnit->agency_id;
    }

    private function agentCanAccessRental(User $user, RentalUnit $rentalUnit): bool
    {
        $userId = (int) $user->getKey();
        $rentalUnitId = (int) $rentalUnit->getKey();
        $propertyId = $rentalUnit->property_id === null ? null : (int) $rentalUnit->property_id;

        if ((int) $rentalUnit->assigned_agent_id === $userId || (int) $rentalUnit->created_by === $userId) {
            return true;
        }

        if ($rentalUnit->contract_id !== null && DB::table('contracts')
            ->where('id', (int) $rentalUnit->contract_id)
            ->where('assigned_agent_id', $userId)
            ->exists()) {
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
                ->exists()
            || DB::table('appointments')
                ->where('property_id', $propertyId)
                ->where('created_by', $userId)
                ->exists()
            || DB::table('rental_parties')
                ->where('rental_unit_id', $rentalUnitId)
                ->whereExists(function ($subquery) use ($userId): void {
                    $subquery->selectRaw('1')
                        ->from('clients')
                        ->whereColumn('clients.id', 'rental_parties.client_id')
                        ->whereExists(function ($nested) use ($userId): void {
                            $nested->selectRaw('1')
                                ->from('client_property_requests')
                                ->whereColumn('client_property_requests.client_id', 'clients.id')
                                ->where('client_property_requests.created_by', $userId);
                        });
                })
                ->exists();
    }
}
