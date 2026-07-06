<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Collaboration;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Provider;
use App\Models\RentalUnit;
use App\Models\User;
use App\Services\SearchService;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function global(SearchRequest $request): JsonResponse
    {
        Gate::authorize('search.view');
        $this->authorizeGlobalSearch($request);

        $keyword = $this->keyword($request);
        $perPage = $this->perPage($request);
        $results = $this->searchService->globalSearch($this->agencyId($request), $keyword, $perPage);

        return response()->json([
            'data' => collect($results)
                ->map(fn (array $items, string $entity): array => $this->normalizeMany($entity, $items, $keyword))
                ->all(),
            'meta' => [
                'keyword' => $keyword,
                'entity' => 'global',
                'per_page' => $perPage,
            ],
        ]);
    }

    public function properties(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Property::class);
        $builder = $this->searchService->searchProperties($this->agencyId($request), $this->filters($request));
        $this->applyVisibility($builder, $request, 'properties');

        return $this->builderResponse(
            $request,
            'properties',
            $builder
        );
    }

    public function owners(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Owner::class);
        $builder = $this->searchService->searchOwners($this->agencyId($request), $this->keyword($request));
        $this->applyVisibility($builder, $request, 'owners');

        return $this->builderResponse(
            $request,
            'owners',
            $builder
        );
    }

    public function clients(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Client::class);
        $builder = $this->searchService->searchClients($this->agencyId($request), $this->keyword($request));
        $this->applyVisibility($builder, $request, 'clients');

        return $this->builderResponse(
            $request,
            'clients',
            $builder
        );
    }

    public function contracts(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Contract::class);
        $builder = $this->searchService->searchContracts($this->agencyId($request), $this->filters($request));
        $this->applyVisibility($builder, $request, 'contracts');

        return $this->builderResponse(
            $request,
            'contracts',
            $builder
        );
    }

    public function rentals(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', RentalUnit::class);

        $keyword = $this->keyword($request);
        $builder = RentalUnit::query()
            ->where('agency_id', $this->agencyId($request))
            ->where(function (Builder $query) use ($keyword): void {
                $query->where('rental_number', 'like', $this->like($keyword))
                    ->orWhere('status', 'like', $this->like($keyword))
                    ->orWhere('deposit_status', 'like', $this->like($keyword))
                    ->orWhere('notes', 'like', $this->like($keyword));
            });
        $this->applyVisibility($builder, $request, 'rentals');

        return $this->builderResponse($request, 'rentals', $builder);
    }

    public function complaints(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Complaint::class);
        $builder = $this->searchService->searchComplaints($this->agencyId($request), $this->filters($request));
        $this->applyVisibility($builder, $request, 'complaints');

        return $this->builderResponse(
            $request,
            'complaints',
            $builder
        );
    }

    public function providers(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Provider::class);
        $builder = $this->searchService->searchProviders($this->agencyId($request), $this->keyword($request));
        $this->applyVisibility($builder, $request, 'providers');

        return $this->builderResponse(
            $request,
            'providers',
            $builder
        );
    }

    public function appointments(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Appointment::class);
        $builder = $this->searchService->searchAppointments($this->agencyId($request), $this->filters($request));
        $this->applyVisibility($builder, $request, 'appointments');

        return $this->builderResponse(
            $request,
            'appointments',
            $builder
        );
    }

    public function collaborations(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Collaboration::class);

        $keyword = $this->keyword($request);
        $builder = Collaboration::query()
            ->where('agency_id', $this->agencyId($request))
            ->where(function (Builder $query) use ($keyword): void {
                $query->where('collaboration_number', 'like', $this->like($keyword))
                    ->orWhere('collaboration_type', 'like', $this->like($keyword))
                    ->orWhere('status', 'like', $this->like($keyword))
                    ->orWhere('request_message', 'like', $this->like($keyword))
                    ->orWhere('rejection_reason', 'like', $this->like($keyword));
            });
        $this->applyVisibility($builder, $request, 'collaborations');

        return $this->builderResponse($request, 'collaborations', $builder);
    }

    public function financial(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', FinancialTransaction::class);

        $keyword = $this->keyword($request);

        // TODO: Delegate to SearchService::searchFinancialTransactions when financial search is added there.
        $this->agencyId($request);

        return response()->json([
            'data' => [],
            'meta' => [
                'keyword' => $keyword,
                'entity' => 'financial',
                'total' => 0,
                'per_page' => $this->perPage($request),
                'current_page' => (int) $request->integer('page', 1),
            ],
        ]);
    }

    public function invoices(SearchRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Invoice::class);
        $builder = $this->searchService->searchInvoices($this->agencyId($request), $this->filters($request));
        $this->applyVisibility($builder, $request, 'invoices');

        return $this->builderResponse(
            $request,
            'invoices',
            $builder
        );
    }

    private function builderResponse(SearchRequest $request, string $entity, Builder $builder): JsonResponse
    {
        $this->applySorting($builder, $request);

        $paginator = $builder->paginate(
            $this->perPage($request),
            ['*'],
            'page',
            (int) $request->integer('page', 1)
        );

        return response()->json([
            'data' => $this->resourceData(
                collect($paginator->items())->map(fn (mixed $item): array => $this->normalize($entity, $this->itemArray($item), $this->keyword($request))),
                $request
            ),
            'meta' => $this->paginationMeta($paginator, $entity, $this->keyword($request)),
        ]);
    }

    private function groupedResponse(SearchRequest $request, string $entity): JsonResponse
    {
        $keyword = $this->keyword($request);
        $items = $this->searchService->globalSearch($this->agencyId($request), $keyword, $this->perPage($request) * 2)[$entity] ?? [];
        $items = $this->filterArrayItems($items, $this->filters($request));
        $paginator = $this->paginateArray($items, $request);

        return response()->json([
            'data' => $this->resourceData(
                collect($paginator->items())->map(fn (array $item): array => $this->normalize($entity, $item, $keyword)),
                $request
            ),
            'meta' => $this->paginationMeta($paginator, $entity, $keyword),
        ]);
    }

    private function resourceData(Collection $items, SearchRequest $request): array
    {
        return SearchResultResource::collection($items)->resolve($request);
    }

    private function agencyId(SearchRequest $request): int
    {
        $agencyId = app(TenantContext::class)->agencyId();
        $user = $request->user();

        if ($agencyId === null && $user instanceof User) {
            $agencyId = $user->agency_id === null ? null : (int) $user->agency_id;
        }

        abort_if($agencyId === null, 403, 'Tenant agency is required.');

        return $agencyId;
    }

    private function currentUser(SearchRequest $request): User
    {
        $user = $request->user();

        abort_if(! $user instanceof User, 401, 'Unauthenticated.');

        return $user;
    }

    private function authorizeGlobalSearch(SearchRequest $request): void
    {
        $user = $this->currentUser($request);

        // TODO: SearchService::globalSearch returns agency-wide arrays; add per-entity query constraints before enabling restricted roles.
        abort_unless($user->hasAnyRole(['manager', 'assistant']), 403, 'Global search requires agency-wide search access.');
    }

    private function applyVisibility(Builder $builder, SearchRequest $request, string $entity): void
    {
        $user = $this->currentUser($request);

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return;
        }

        if (! $user->hasRole('agent')) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $userId = (int) $user->getKey();

        match ($entity) {
            'properties' => $this->agentProperties($builder, $userId),
            'owners' => $this->agentOwners($builder, $userId),
            'clients' => $this->agentClients($builder, $userId),
            'contracts' => $this->agentContracts($builder, $userId),
            'rentals' => $this->agentRentals($builder, $userId),
            'complaints' => $this->agentComplaints($builder, $userId),
            'appointments' => $this->agentAppointments($builder, $userId),
            'collaborations' => $this->agentCollaborations($builder, $userId),
            'invoices' => $this->agentInvoices($builder, $userId),
            default => $builder->whereRaw('1 = 0'),
        };
    }

    private function agentProperties(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $query) use ($userId): void {
            $query->where('created_by', $userId)
                ->orWhere('updated_by', $userId)
                ->orWhereExists(fn ($subquery) => $subquery->selectRaw('1')->from('contracts')->whereColumn('contracts.property_id', 'properties.id')->where('contracts.assigned_agent_id', $userId))
                ->orWhereExists(fn ($subquery) => $subquery->selectRaw('1')->from('rental_units')->whereColumn('rental_units.property_id', 'properties.id')->where('rental_units.assigned_agent_id', $userId))
                ->orWhereExists(fn ($subquery) => $subquery->selectRaw('1')->from('complaints')->whereColumn('complaints.property_id', 'properties.id')->where('complaints.assigned_to', $userId))
                ->orWhereExists(fn ($subquery) => $subquery->selectRaw('1')->from('collaborations')->whereColumn('collaborations.property_id', 'properties.id')->where(fn ($collaboration) => $collaboration->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
        });
    }

    private function agentOwners(Builder $builder, int $userId): void
    {
        $builder->whereExists(function ($subquery) use ($userId): void {
            $subquery->selectRaw('1')
                ->from('property_owners')
                ->join('properties', 'properties.id', '=', 'property_owners.property_id')
                ->whereColumn('property_owners.owner_id', 'owners.id')
                ->where(function ($query) use ($userId): void {
                    $query->where('properties.created_by', $userId)
                        ->orWhere('properties.updated_by', $userId)
                        ->orWhereExists(fn ($contract) => $contract->selectRaw('1')->from('contracts')->whereColumn('contracts.property_id', 'properties.id')->where('contracts.assigned_agent_id', $userId))
                        ->orWhereExists(fn ($rental) => $rental->selectRaw('1')->from('rental_units')->whereColumn('rental_units.property_id', 'properties.id')->where('rental_units.assigned_agent_id', $userId))
                        ->orWhereExists(fn ($complaint) => $complaint->selectRaw('1')->from('complaints')->whereColumn('complaints.property_id', 'properties.id')->where('complaints.assigned_to', $userId))
                        ->orWhereExists(fn ($collaboration) => $collaboration->selectRaw('1')->from('collaborations')->whereColumn('collaborations.property_id', 'properties.id')->where(fn ($query) => $query->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
                });
        });
    }

    private function agentClients(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $query) use ($userId): void {
            $query->whereExists(fn ($contract) => $contract->selectRaw('1')->from('contract_parties')->join('contracts', 'contracts.id', '=', 'contract_parties.contract_id')->whereColumn('contract_parties.client_id', 'clients.id')->where('contracts.assigned_agent_id', $userId))
                ->orWhereExists(fn ($rental) => $rental->selectRaw('1')->from('rental_parties')->join('rental_units', 'rental_units.id', '=', 'rental_parties.rental_unit_id')->whereColumn('rental_parties.client_id', 'clients.id')->where('rental_units.assigned_agent_id', $userId))
                ->orWhereExists(fn ($complaint) => $complaint->selectRaw('1')->from('complaints')->whereColumn('complaints.client_id', 'clients.id')->where('complaints.assigned_to', $userId))
                ->orWhereExists(fn ($appointment) => $appointment->selectRaw('1')->from('appointments')->whereColumn('appointments.client_id', 'clients.id')->where('appointments.created_by', $userId))
                ->orWhereExists(fn ($collaboration) => $collaboration->selectRaw('1')->from('collaborations')->whereColumn('collaborations.client_id', 'clients.id')->where(fn ($query) => $query->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
        });
    }

    private function agentContracts(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $query) use ($userId): void {
            $query->where('assigned_agent_id', $userId)
                ->orWhere('created_by', $userId)
                ->orWhereExists(fn ($property) => $property->selectRaw('1')->from('properties')->whereColumn('properties.id', 'contracts.property_id')->where(fn ($query) => $query->where('properties.created_by', $userId)->orWhere('properties.updated_by', $userId)))
                ->orWhereExists(fn ($rental) => $rental->selectRaw('1')->from('rental_units')->whereColumn('rental_units.contract_id', 'contracts.id')->where('rental_units.assigned_agent_id', $userId))
                ->orWhereExists(fn ($complaint) => $complaint->selectRaw('1')->from('complaints')->whereColumn('complaints.property_id', 'contracts.property_id')->where('complaints.assigned_to', $userId))
                ->orWhereExists(fn ($collaboration) => $collaboration->selectRaw('1')->from('collaborations')->whereColumn('collaborations.property_id', 'contracts.property_id')->where(fn ($query) => $query->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
        });
    }

    private function agentRentals(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $query) use ($userId): void {
            $query->where('assigned_agent_id', $userId)
                ->orWhere('created_by', $userId)
                ->orWhereExists(fn ($contract) => $contract->selectRaw('1')->from('contracts')->whereColumn('contracts.id', 'rental_units.contract_id')->where('contracts.assigned_agent_id', $userId))
                ->orWhereExists(fn ($property) => $property->selectRaw('1')->from('properties')->whereColumn('properties.id', 'rental_units.property_id')->where(fn ($query) => $query->where('properties.created_by', $userId)->orWhere('properties.updated_by', $userId)))
                ->orWhereExists(fn ($complaint) => $complaint->selectRaw('1')->from('complaints')->whereColumn('complaints.property_id', 'rental_units.property_id')->where('complaints.assigned_to', $userId))
                ->orWhereExists(fn ($collaboration) => $collaboration->selectRaw('1')->from('collaborations')->whereColumn('collaborations.property_id', 'rental_units.property_id')->where(fn ($query) => $query->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
        });
    }

    private function agentComplaints(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $query) use ($userId): void {
            $query->where('assigned_to', $userId)
                ->orWhere('created_by', $userId)
                ->orWhereExists(fn ($property) => $property->selectRaw('1')->from('properties')->whereColumn('properties.id', 'complaints.property_id')->where(fn ($query) => $query->where('properties.created_by', $userId)->orWhere('properties.updated_by', $userId)))
                ->orWhereExists(fn ($contract) => $contract->selectRaw('1')->from('contracts')->whereColumn('contracts.property_id', 'complaints.property_id')->where('contracts.assigned_agent_id', $userId))
                ->orWhereExists(fn ($rental) => $rental->selectRaw('1')->from('rental_units')->whereColumn('rental_units.property_id', 'complaints.property_id')->where('rental_units.assigned_agent_id', $userId))
                ->orWhereExists(fn ($collaboration) => $collaboration->selectRaw('1')->from('collaborations')->whereColumn('collaborations.property_id', 'complaints.property_id')->where(fn ($query) => $query->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
        });
    }

    private function agentAppointments(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $query) use ($userId): void {
            $query->where('created_by', $userId)
                ->orWhereExists(fn ($participant) => $participant->selectRaw('1')->from('appointment_participants')->whereColumn('appointment_participants.appointment_id', 'appointments.id')->where('appointment_participants.user_id', $userId))
                ->orWhereExists(fn ($contract) => $contract->selectRaw('1')->from('contracts')->whereColumn('contracts.id', 'appointments.contract_id')->where('contracts.assigned_agent_id', $userId))
                ->orWhereExists(fn ($rental) => $rental->selectRaw('1')->from('rental_units')->whereColumn('rental_units.contract_id', 'appointments.contract_id')->where('rental_units.assigned_agent_id', $userId))
                ->orWhereExists(fn ($complaint) => $complaint->selectRaw('1')->from('complaints')->whereColumn('complaints.id', 'appointments.complaint_id')->where(fn ($query) => $query->where('complaints.assigned_to', $userId)->orWhere('complaints.created_by', $userId)))
                ->orWhereExists(fn ($property) => $property->selectRaw('1')->from('properties')->whereColumn('properties.id', 'appointments.property_id')->where(fn ($query) => $query->where('properties.created_by', $userId)->orWhere('properties.updated_by', $userId)))
                ->orWhereExists(fn ($collaboration) => $collaboration->selectRaw('1')->from('collaborations')->whereColumn('collaborations.id', 'appointments.collaboration_id')->where(fn ($query) => $query->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
        });
    }

    private function agentCollaborations(Builder $builder, int $userId): void
    {
        $builder->where(fn (Builder $query) => $query->where('requesting_agent_id', $userId)->orWhere('owner_agent_id', $userId)->orWhere('created_by', $userId));
    }

    private function agentInvoices(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $query) use ($userId): void {
            $query->where('created_by', $userId)
                ->orWhereExists(fn ($contract) => $contract->selectRaw('1')->from('contracts')->whereColumn('contracts.id', 'invoices.contract_id')->where(fn ($query) => $query->where('contracts.assigned_agent_id', $userId)->orWhere('contracts.created_by', $userId)))
                ->orWhereExists(fn ($property) => $property->selectRaw('1')->from('contracts')->join('properties', 'properties.id', '=', 'contracts.property_id')->whereColumn('contracts.id', 'invoices.contract_id')->where(fn ($query) => $query->where('properties.created_by', $userId)->orWhere('properties.updated_by', $userId)))
                ->orWhereExists(fn ($rental) => $rental->selectRaw('1')->from('rental_units')->whereColumn('rental_units.contract_id', 'invoices.contract_id')->where('rental_units.assigned_agent_id', $userId))
                ->orWhereExists(fn ($complaint) => $complaint->selectRaw('1')->from('contracts')->join('complaints', 'complaints.property_id', '=', 'contracts.property_id')->whereColumn('contracts.id', 'invoices.contract_id')->where('complaints.assigned_to', $userId))
                ->orWhereExists(fn ($collaboration) => $collaboration->selectRaw('1')->from('contracts')->join('collaborations', 'collaborations.property_id', '=', 'contracts.property_id')->whereColumn('contracts.id', 'invoices.contract_id')->where(fn ($query) => $query->where('collaborations.requesting_agent_id', $userId)->orWhere('collaborations.owner_agent_id', $userId)->orWhere('collaborations.created_by', $userId)));
        });
    }

    private function keyword(SearchRequest $request): string
    {
        return trim((string) $request->input('keyword', ''));
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(SearchRequest $request): array
    {
        $filters = $request->input('filters', []);
        $filters = is_array($filters) ? $filters : [];
        $filters['keyword'] ??= $this->keyword($request);

        return $filters;
    }

    private function perPage(SearchRequest $request): int
    {
        return max(1, min(100, (int) $request->integer('per_page', 15)));
    }

    private function applySorting(Builder $builder, SearchRequest $request): void
    {
        $sort = $request->input('sort');

        if (! is_string($sort) || $sort === '') {
            return;
        }

        // TODO: Whitelist sortable columns per entity when search UI contracts are finalized.
        if (! preg_match('/^[A-Za-z0-9_.]+$/', $sort)) {
            return;
        }

        $direction = strtolower((string) $request->input('direction', 'asc'));
        $builder->orderBy($sort, in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc');
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    private function filterArrayItems(array $items, array $filters): array
    {
        return array_values(array_filter($items, static function (array $item) use ($filters): bool {
            foreach ($filters as $key => $value) {
                if ($key === 'keyword' || $value === null || $value === '') {
                    continue;
                }

                if (array_key_exists($key, $item) && (string) $item[$key] !== (string) $value) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function paginateArray(array $items, SearchRequest $request): LengthAwarePaginator
    {
        $page = (int) $request->integer('page', 1);
        $perPage = $this->perPage($request);

        return new LengthAwarePaginator(
            array_slice($items, ($page - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $page
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function itemArray(mixed $item): array
    {
        if (is_array($item)) {
            return $item;
        }

        return method_exists($item, 'toArray') ? $item->toArray() : [];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeMany(string $entity, array $items, string $keyword): array
    {
        return array_map(fn (array $item): array => $this->normalize($entity, $item, $keyword), $items);
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function normalize(string $entity, array $item, string $keyword): array
    {
        return [
            'entity' => $entity,
            'entity_type' => rtrim($entity, 's'),
            'id' => $item['id'] ?? null,
            'title' => $this->titleFor($entity, $item),
            'subtitle' => $this->subtitleFor($entity, $item),
            'status' => $item['status'] ?? null,
            'url' => sprintf('/%s/%s', str_replace('_', '-', $entity), (string) ($item['id'] ?? '')),
            'score' => $keyword === '' ? null : 1.0,
            'highlight' => $keyword,
            'metadata' => $item,
        ];
    }

    /**
     * @param array<string, mixed> $item
     */
    private function titleFor(string $entity, array $item): ?string
    {
        foreach (['title', 'name', 'full_name', 'company_name', 'reference', 'contract_number', 'rental_number', 'complaint_number', 'appointment_number', 'collaboration_number', 'invoice_number', 'transaction_number'] as $key) {
            if (! empty($item[$key])) {
                return (string) $item[$key];
            }
        }

        return isset($item['id']) ? sprintf('%s #%s', ucfirst(rtrim($entity, 's')), (string) $item['id']) : null;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function subtitleFor(string $entity, array $item): ?string
    {
        foreach (['description', 'email', 'phone', 'city', 'contract_type', 'complaint_type', 'appointment_type', 'collaboration_type', 'notes'] as $key) {
            if (! empty($item[$key])) {
                return (string) $item[$key];
            }
        }

        return ucfirst(str_replace('_', ' ', $entity));
    }

    /**
     * @return array<string, mixed>
     */
    private function paginationMeta(LengthAwarePaginator $paginator, string $entity, string $keyword): array
    {
        return [
            'keyword' => $keyword,
            'entity' => $entity,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
