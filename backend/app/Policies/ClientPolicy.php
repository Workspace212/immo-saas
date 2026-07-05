<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user)) {
            return false;
        }

        return $this->hasInternalRole($user)
            && $this->hasAgency($user)
            && $user->can('clients.viewAny');
    }

    public function view(User $user, Client $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('clients.view')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessClient($user, $model);
        }

        if ($user->hasRole('client')) {
            // TODO: clients table has no user_id; cannot safely authorize own Client record yet.
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
            && $user->can('clients.create');
    }

    public function update(User $user, Client $model): bool
    {
        if ($this->isSuperAdmin($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if (! $user->can('clients.update')) {
            return false;
        }

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $this->agentCanAccessClient($user, $model);
        }

        if ($user->hasRole('client')) {
            // TODO: clients table has no user_id, and protected identity/legal/internal fields are not field-authorized yet.
            return false;
        }

        return false;
    }

    public function delete(User $user, Client $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('clients.delete');
    }

    public function archive(User $user, Client $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('clients.archive');
    }

    public function restore(User $user, Client $model): bool
    {
        if ($this->isSuperAdmin($user) || $this->hasExternalRole($user) || ! $this->sameAgency($user, $model)) {
            return false;
        }

        if ($user->hasRole('agent')) {
            return false;
        }

        return $user->hasAnyRole(['manager', 'assistant', 'employee'])
            && $user->can('clients.restore');
    }

    public function forceDelete(User $user, Client $model): bool
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

    private function sameAgency(User $user, Client $client): bool
    {
        return $user->agency_id !== null
            && $client->agency_id !== null
            && (int) $user->agency_id === (int) $client->agency_id;
    }

    private function agentCanAccessClient(User $user, Client $client): bool
    {
        $userId = (int) $user->getKey();
        $clientId = (int) $client->getKey();

        return DB::table('contract_parties')
            ->join('contracts', 'contracts.id', '=', 'contract_parties.contract_id')
            ->where('contract_parties.client_id', $clientId)
            ->where('contracts.assigned_agent_id', $userId)
            ->exists()
            || DB::table('rental_parties')
                ->join('rental_units', 'rental_units.id', '=', 'rental_parties.rental_unit_id')
                ->where('rental_parties.client_id', $clientId)
                ->where('rental_units.assigned_agent_id', $userId)
                ->exists()
            || DB::table('complaints')
                ->where('client_id', $clientId)
                ->where('assigned_to', $userId)
                ->exists()
            || DB::table('appointments')
                ->where('client_id', $clientId)
                ->where('created_by', $userId)
                ->exists()
            || DB::table('collaborations')
                ->where('client_id', $clientId)
                ->where(function ($query) use ($userId): void {
                    $query->where('requesting_agent_id', $userId)
                        ->orWhere('owner_agent_id', $userId)
                        ->orWhere('created_by', $userId);
                })
                ->exists();
    }
}
