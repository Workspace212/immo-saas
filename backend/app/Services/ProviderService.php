<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProviderService
{
    public function create(array $data, ?User $user = null): Provider
    {
        return DB::transaction(function () use ($data, $user): Provider {
            $providerData = $this->onlyProviderData($data);
            $providerData['is_active'] ??= true;

            // TODO: Validate duplicate providers by ICE/RC/email/phone per agency.
            unset($user);

            return Provider::query()->create($providerData);
        });
    }

    public function update(Provider $provider, array $data, ?User $user = null): Provider
    {
        return DB::transaction(function () use ($provider, $data, $user): Provider {
            $providerData = $this->onlyProviderData($data);

            // TODO: Add provider change audit logging when the audit workflow is finalized.
            unset($user);

            if ($providerData !== []) {
                $provider->fill($providerData);
                $provider->save();
            }

            return $provider->refresh();
        });
    }

    public function activate(Provider $provider): Provider
    {
        return DB::transaction(function () use ($provider): Provider {
            // TODO: Validate provider compliance status before reactivation.
            $provider->fill(['is_active' => true]);
            $provider->save();

            return $provider->refresh();
        });
    }

    public function deactivate(Provider $provider): Provider
    {
        return DB::transaction(function () use ($provider): Provider {
            // TODO: Prevent deactivation when open interventions require the provider.
            $provider->fill(['is_active' => false]);
            $provider->save();

            return $provider->refresh();
        });
    }

    public function generateProviderNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('PROV-%s-', $year);

        // TODO: Persist provider numbers if the providers table receives a provider_number column.
        $count = Provider::query()
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('%s%06d', $prefix, $count + 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function onlyProviderData(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'agency_id',
            'provider_type',
            'company_name',
            'contact_name',
            'phone',
            'whatsapp',
            'email',
            'address',
            'city',
            'ice',
            'rc',
            'if_number',
            'patente',
            'rating',
            'is_active',
            'notes',
        ]));
    }
}
