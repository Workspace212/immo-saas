<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Collaboration;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyActivity;
use App\Models\Provider;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function globalSearch(int $agencyId, string $query, int $limit = 20): array
    {
        $limit = max(1, $limit);

        return [
            'properties' => $this->searchProperties($agencyId, ['keyword' => $query])->limit($limit)->get()->toArray(),
            'contracts' => $this->searchContracts($agencyId, ['keyword' => $query])->limit($limit)->get()->toArray(),
            'rentals' => $this->searchRentals($agencyId, $query, $limit),
            'clients' => $this->searchClients($agencyId, $query)->limit($limit)->get()->toArray(),
            'owners' => $this->searchOwners($agencyId, $query)->limit($limit)->get()->toArray(),
            'agents' => $this->searchAgents($agencyId, $query)->limit($limit)->get()->toArray(),
            'complaints' => $this->searchComplaints($agencyId, ['keyword' => $query])->limit($limit)->get()->toArray(),
            'providers' => $this->searchProviders($agencyId, $query)->limit($limit)->get()->toArray(),
            'invoices' => $this->searchInvoices($agencyId, ['keyword' => $query])->limit($limit)->get()->toArray(),
            'collaborations' => $this->searchCollaborations($agencyId, $query, $limit),
            'appointments' => $this->searchAppointments($agencyId, ['keyword' => $query])->limit($limit)->get()->toArray(),
        ];
    }

    public function searchProperties(int $agencyId, array $filters): Builder
    {
        return $this->buildPropertyQuery($agencyId, $filters);
    }

    public function searchClients(int $agencyId, string $query): Builder
    {
        return $this->personSearch(Client::query()->where('agency_id', $agencyId), $query);
    }

    public function searchOwners(int $agencyId, string $query): Builder
    {
        return $this->personSearch(Owner::query()->where('agency_id', $agencyId), $query);
    }

    public function searchAgents(int $agencyId, string $query): Builder
    {
        return User::query()
            ->where('agency_id', $agencyId)
            ->where(function (Builder $builder) use ($query): void {
                $this->whereLike($builder, ['name', 'email', 'phone', 'status'], $query);
            });
    }

    public function searchContracts(int $agencyId, array $filters): Builder
    {
        $builder = Contract::query()->where('agency_id', $agencyId);

        $this->applyExactFilters($builder, $filters, [
            'status',
            'contract_type',
            'property_id',
            'assigned_agent_id',
        ]);

        $this->applyDateRange($builder, $filters, 'start_date', 'start_from', 'start_to');
        $this->applyDateRange($builder, $filters, 'end_date', 'end_from', 'end_to');
        $this->applyAmountRange($builder, $filters, 'amount', 'amount_min', 'amount_max');

        if (! empty($filters['keyword'])) {
            $builder->where(function (Builder $query) use ($filters): void {
                $this->whereLike($query, ['contract_number', 'contract_type', 'status', 'notes'], (string) $filters['keyword']);
            });
        }

        return $builder;
    }

    public function searchComplaints(int $agencyId, array $filters): Builder
    {
        $builder = Complaint::query()->where('agency_id', $agencyId);

        $this->applyExactFilters($builder, $filters, [
            'status',
            'priority',
            'complaint_type',
            'property_id',
            'client_id',
            'assigned_to',
        ]);

        $this->applyDateRange($builder, $filters, 'created_at', 'created_from', 'created_to');

        if (! empty($filters['keyword'])) {
            $builder->where(function (Builder $query) use ($filters): void {
                $this->whereLike($query, ['complaint_number', 'title', 'description', 'status', 'priority'], (string) $filters['keyword']);
            });
        }

        return $builder;
    }

    public function searchAppointments(int $agencyId, array $filters): Builder
    {
        $builder = Appointment::query()->where('agency_id', $agencyId);

        $this->applyExactFilters($builder, $filters, [
            'status',
            'priority',
            'appointment_type',
            'property_id',
            'client_id',
            'owner_id',
            'provider_id',
            'created_by',
        ]);

        $this->applyDateRange($builder, $filters, 'start_at', 'start_from', 'start_to');

        if (! empty($filters['keyword'])) {
            $builder->where(function (Builder $query) use ($filters): void {
                $this->whereLike($query, ['appointment_number', 'title', 'description', 'location', 'status'], (string) $filters['keyword']);
            });
        }

        return $builder;
    }

    public function searchInvoices(int $agencyId, array $filters): Builder
    {
        $builder = Invoice::query()->where('agency_id', $agencyId);

        $this->applyExactFilters($builder, $filters, [
            'status',
            'client_id',
            'owner_id',
            'contract_id',
        ]);

        $this->applyDateRange($builder, $filters, 'issued_at', 'issued_from', 'issued_to');
        $this->applyDateRange($builder, $filters, 'due_at', 'due_from', 'due_to');
        $this->applyAmountRange($builder, $filters, 'total_amount', 'amount_min', 'amount_max');

        if (! empty($filters['keyword'])) {
            $builder->where(function (Builder $query) use ($filters): void {
                $this->whereLike($query, ['invoice_number', 'status', 'currency', 'notes'], (string) $filters['keyword']);
            });
        }

        return $builder;
    }

    public function searchProviders(int $agencyId, string $query): Builder
    {
        return Provider::query()
            ->where('agency_id', $agencyId)
            ->where(function (Builder $builder) use ($query): void {
                $this->whereLike($builder, [
                    'provider_type',
                    'company_name',
                    'contact_name',
                    'phone',
                    'whatsapp',
                    'email',
                    'city',
                    'ice',
                    'notes',
                ], $query);
            });
    }

    public function getRecentSearches(User $user): array
    {
        // TODO: Persist and return recent searches once a search history table is available.
        return [];
    }

    public function saveRecentSearch(User $user, string $query): void
    {
        // TODO: Save recent search when a search history model/table is introduced.
        unset($user, $query);
    }

    public function buildPropertyQuery(int $agencyId, array $filters): Builder
    {
        $builder = Property::query()->where('agency_id', $agencyId);

        $this->applyExactFilters($builder, $filters, [
            'city',
            'status',
            'bedrooms',
            'bathrooms',
        ]);

        if (! empty($filters['district'])) {
            $builder->where('sector', $filters['district']);
        }

        if (! empty($filters['property_type'])) {
            $builder->where('property_type_id', $filters['property_type']);
        }

        $this->applyAmountRange($builder, $filters, 'living_area_m2', 'surface_min', 'surface_max');

        if (! empty($filters['transaction_type'])) {
            $builder->whereIn('id', PropertyActivity::query()
                ->select('property_id')
                ->where('activity_type', $filters['transaction_type']));
        }

        if (isset($filters['price_min']) || isset($filters['price_max'])) {
            $activityQuery = PropertyActivity::query()->select('property_id');

            if (isset($filters['price_min'])) {
                $activityQuery->where('price', '>=', $filters['price_min']);
            }

            if (isset($filters['price_max'])) {
                $activityQuery->where('price', '<=', $filters['price_max']);
            }

            $builder->whereIn('id', $activityQuery);
        }

        foreach (['parking', 'elevator', 'pool', 'garden', 'furnished'] as $futureAmenity) {
            if (array_key_exists($futureAmenity, $filters)) {
                // TODO: Apply amenity filters when property amenities are modeled.
            }
        }

        if (! empty($filters['keyword'])) {
            $builder->where(function (Builder $query) use ($filters): void {
                $this->whereLike($query, ['reference', 'title', 'description', 'city', 'sector', 'address'], (string) $filters['keyword']);
            });
        }

        return $builder;
    }

    public function autocomplete(int $agencyId, string $query): array
    {
        return [
            'properties' => Property::query()
                ->where('agency_id', $agencyId)
                ->where(function (Builder $builder) use ($query): void {
                    $this->whereLike($builder, ['reference', 'title'], $query);
                })
                ->limit(8)
                ->get(['id', 'reference', 'title', 'city', 'sector'])
                ->toArray(),
            'clients' => $this->searchClients($agencyId, $query)->limit(8)->get(['id', 'full_name', 'company_name', 'email', 'phone'])->toArray(),
            'owners' => $this->searchOwners($agencyId, $query)->limit(8)->get(['id', 'full_name', 'company_name', 'email', 'phone'])->toArray(),
            'agents' => $this->searchAgents($agencyId, $query)->limit(8)->get(['id', 'name', 'email', 'phone'])->toArray(),
            'cities' => Property::query()
                ->where('agency_id', $agencyId)
                ->where('city', 'like', $this->like($query))
                ->distinct()
                ->limit(8)
                ->pluck('city')
                ->filter()
                ->values()
                ->toArray(),
            'districts' => Property::query()
                ->where('agency_id', $agencyId)
                ->where('sector', 'like', $this->like($query))
                ->distinct()
                ->limit(8)
                ->pluck('sector')
                ->filter()
                ->values()
                ->toArray(),
        ];
    }

    public function advancedSearch(int $agencyId, array $filters): array
    {
        // TODO: Add weighting, saved filters, and full-text indexes when the search UX is finalized.
        return [
            'properties' => $this->buildPropertyQuery($agencyId, $filters['properties'] ?? $filters)->get()->toArray(),
            'contracts' => $this->searchContracts($agencyId, $filters['contracts'] ?? [])->get()->toArray(),
            'clients' => isset($filters['clients']['keyword'])
                ? $this->searchClients($agencyId, (string) $filters['clients']['keyword'])->get()->toArray()
                : [],
            'owners' => isset($filters['owners']['keyword'])
                ? $this->searchOwners($agencyId, (string) $filters['owners']['keyword'])->get()->toArray()
                : [],
            'complaints' => $this->searchComplaints($agencyId, $filters['complaints'] ?? [])->get()->toArray(),
            'appointments' => $this->searchAppointments($agencyId, $filters['appointments'] ?? [])->get()->toArray(),
            'invoices' => $this->searchInvoices($agencyId, $filters['invoices'] ?? [])->get()->toArray(),
        ];
    }

    private function searchRentals(int $agencyId, string $query, int $limit): array
    {
        return RentalUnit::query()
            ->where('agency_id', $agencyId)
            ->where(function (Builder $builder) use ($query): void {
                $this->whereLike($builder, ['rental_number', 'status', 'deposit_status', 'notes'], $query);
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function searchCollaborations(int $agencyId, string $query, int $limit): array
    {
        return Collaboration::query()
            ->where('agency_id', $agencyId)
            ->where(function (Builder $builder) use ($query): void {
                $this->whereLike($builder, [
                    'collaboration_number',
                    'collaboration_type',
                    'status',
                    'request_message',
                    'rejection_reason',
                ], $query);
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function personSearch(Builder $builder, string $query): Builder
    {
        return $builder->where(function (Builder $search) use ($query): void {
            $this->whereLike($search, [
                'full_name',
                'company_name',
                'cin_passport',
                'phone',
                'whatsapp',
                'email',
                'city',
                'ice',
                'notes',
                'status',
            ], $query);
        });
    }

    private function applyExactFilters(Builder $builder, array $filters, array $columns): void
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $filters) && $filters[$column] !== null && $filters[$column] !== '') {
                $builder->where($column, $filters[$column]);
            }
        }
    }

    private function applyDateRange(
        Builder $builder,
        array $filters,
        string $column,
        string $fromKey,
        string $toKey
    ): void {
        if (! empty($filters[$fromKey])) {
            $builder->where($column, '>=', $filters[$fromKey]);
        }

        if (! empty($filters[$toKey])) {
            $builder->where($column, '<=', $filters[$toKey]);
        }
    }

    private function applyAmountRange(
        Builder $builder,
        array $filters,
        string $column,
        string $minKey,
        string $maxKey
    ): void {
        if (isset($filters[$minKey])) {
            $builder->where($column, '>=', $filters[$minKey]);
        }

        if (isset($filters[$maxKey])) {
            $builder->where($column, '<=', $filters[$maxKey]);
        }
    }

    private function whereLike(Builder $builder, array $columns, string $query): void
    {
        $like = $this->like($query);

        foreach ($columns as $index => $column) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $builder->{$method}($column, 'like', $like);
        }
    }

    private function like(string $query): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($query)) . '%';
    }
}
