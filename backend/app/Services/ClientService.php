<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClientService
{
    public function create(array $data, ?User $user = null): Client
    {
        return DB::transaction(function () use ($data, $user): Client {
            $clientData = $this->normalizeClientData($data);
            $clientData['status'] ??= 'active';

            // TODO: Validate duplicate clients by email, phone, CIN/passport, and agency policy.
            unset($user);

            return Client::query()->create($clientData);
        });
    }

    public function update(Client $client, array $data, ?User $user = null): Client
    {
        return DB::transaction(function () use ($client, $data, $user): Client {
            $clientData = $this->normalizeClientData($data);

            // TODO: Add client change audit logging when the audit workflow is finalized.
            unset($user);

            if ($clientData !== []) {
                $client->fill($clientData);
                $client->save();
            }

            return $client->refresh();
        });
    }

    public function archive(Client $client): Client
    {
        return DB::transaction(function () use ($client): Client {
            // TODO: Prevent archiving clients linked to active rentals or unpaid invoices if required.
            $client->fill(['status' => 'archived']);
            $client->save();

            return $client->refresh();
        });
    }

    public function restore(Client $client): Client
    {
        return DB::transaction(function () use ($client): Client {
            // TODO: Restore related records when a formal client archive workflow exists.
            if (method_exists($client, 'trashed') && $client->trashed()) {
                $client->restore();
            }

            $client->fill(['status' => 'active']);
            $client->save();

            return $client->refresh();
        });
    }

    public function assignAgent(Client $client, User $agent): Client
    {
        return DB::transaction(function () use ($client, $agent): Client {
            // TODO: Persist assigned agents when clients receive an assigned_agent_id column or relation.
            unset($agent);

            return $client->refresh();
        });
    }

    public function delete(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            // TODO: Enforce deletion rules for clients linked to contracts, invoices, or complaints.
            $client->delete();
        });
    }

    public function generateClientNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('CLIENT-%s-', $year);

        // TODO: Persist client numbers if the clients table receives a client_number column.
        $count = Client::query()
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('%s%06d', $prefix, $count + 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeClientData(array $data): array
    {
        if (isset($data['client_type']) && ! isset($data['type'])) {
            $data['type'] = $data['client_type'];
        }

        if (! isset($data['full_name'])) {
            $fullName = trim((string) (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')));

            if ($fullName !== '') {
                $data['full_name'] = $fullName;
            }
        }

        if (! isset($data['cin_passport'])) {
            $data['cin_passport'] = $data['cin'] ?? $data['passport'] ?? null;
        }

        return array_intersect_key($data, array_flip([
            'agency_id',
            'type',
            'full_name',
            'cin_passport',
            'phone',
            'whatsapp',
            'email',
            'address',
            'city',
            'country',
            'company_name',
            'ice',
            'rc',
            'if_number',
            'patente',
            'representative_name',
            'notes',
            'status',
        ]));
    }
}
