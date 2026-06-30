<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function global(SearchRequest $request): JsonResponse
    {
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
        return $this->builderResponse(
            $request,
            'properties',
            $this->searchService->searchProperties($this->agencyId($request), $this->filters($request))
        );
    }

    public function owners(SearchRequest $request): JsonResponse
    {
        return $this->builderResponse(
            $request,
            'owners',
            $this->searchService->searchOwners($this->agencyId($request), $this->keyword($request))
        );
    }

    public function clients(SearchRequest $request): JsonResponse
    {
        return $this->builderResponse(
            $request,
            'clients',
            $this->searchService->searchClients($this->agencyId($request), $this->keyword($request))
        );
    }

    public function contracts(SearchRequest $request): JsonResponse
    {
        return $this->builderResponse(
            $request,
            'contracts',
            $this->searchService->searchContracts($this->agencyId($request), $this->filters($request))
        );
    }

    public function rentals(SearchRequest $request): JsonResponse
    {
        return $this->groupedResponse($request, 'rentals');
    }

    public function complaints(SearchRequest $request): JsonResponse
    {
        return $this->builderResponse(
            $request,
            'complaints',
            $this->searchService->searchComplaints($this->agencyId($request), $this->filters($request))
        );
    }

    public function providers(SearchRequest $request): JsonResponse
    {
        return $this->builderResponse(
            $request,
            'providers',
            $this->searchService->searchProviders($this->agencyId($request), $this->keyword($request))
        );
    }

    public function appointments(SearchRequest $request): JsonResponse
    {
        return $this->builderResponse(
            $request,
            'appointments',
            $this->searchService->searchAppointments($this->agencyId($request), $this->filters($request))
        );
    }

    public function collaborations(SearchRequest $request): JsonResponse
    {
        return $this->groupedResponse($request, 'collaborations');
    }

    public function financial(SearchRequest $request): JsonResponse
    {
        $keyword = $this->keyword($request);

        // TODO: Delegate to SearchService::searchFinancialTransactions when financial search is added there.
        $this->searchService->advancedSearch($this->agencyId($request), ['keyword' => $keyword]);

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
        return $this->builderResponse(
            $request,
            'invoices',
            $this->searchService->searchInvoices($this->agencyId($request), $this->filters($request))
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
        $user = $request->user();

        return (int) ($request->integer('agency_id') ?: ($user instanceof User ? $user->agency_id : 0));
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
        $builder->orderBy($sort, (string) $request->input('direction', 'asc'));
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
