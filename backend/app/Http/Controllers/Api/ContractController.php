<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\User;
use App\Services\ContractService;
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ContractService $contractService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Contract::class);

        $query = Contract::query()
            ->with(['property', 'creator', 'assignedAgent'])
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        foreach (['status', 'contract_type', 'property_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('owner_id')) {
            $query->whereIn('id', ContractParty::query()
                ->select('contract_id')
                ->where('owner_id', $request->integer('owner_id')));
        }

        if ($request->filled('client_id')) {
            $query->whereIn('id', ContractParty::query()
                ->select('contract_id')
                ->where('client_id', $request->integer('client_id')));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->date('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->date('end_date'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('contract_number', 'like', $keyword)
                    ->orWhere('contract_type', 'like', $keyword)
                    ->orWhere('status', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return ContractResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreContractRequest $request): JsonResponse
    {
        $this->authorize('create', Contract::class);

        $user = $request->user();
        $contract = $this->contractService->createContract(
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return (new ContractResource($this->freshContract($contract)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Contract $contract): ContractResource
    {
        $this->authorize('view', $contract);

        return new ContractResource($this->freshContract($contract));
    }

    public function update(UpdateContractRequest $request, Contract $contract): ContractResource
    {
        $this->authorize('update', $contract);

        $user = $request->user();
        $contract = $this->contractService->updateContract(
            $contract,
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return new ContractResource($this->freshContract($contract));
    }

    public function destroy(Contract $contract): JsonResponse
    {
        $this->authorize('delete', $contract);

        $contract->delete();

        return response()->json([
            'message' => 'Contract deleted successfully.',
        ]);
    }

    public function renew(Contract $contract): JsonResponse
    {
        $this->authorize('renew', $contract);

        $renewedContract = $this->contractService->renewContract($contract, []);

        return (new ContractResource($this->freshContract($renewedContract)))
            ->response()
            ->setStatusCode(201);
    }

    public function archive(Contract $contract): ContractResource
    {
        $this->authorize('archive', $contract);

        $contract = $this->contractService->expireContract($contract);

        return new ContractResource($this->freshContract($contract));
    }

    private function freshContract(Contract $contract): Contract
    {
        return $contract->refresh()->load(['property', 'creator', 'assignedAgent']);
    }

    private function applyIndexAuthorization($query, Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401, 'Unauthenticated.');

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return;
        }

        if ($user->hasRole('agent')) {
            $userId = (int) $user->getKey();

            $query->where(function ($builder) use ($userId): void {
                $builder->where('assigned_agent_id', $userId)
                    ->orWhere('created_by', $userId)
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('properties')
                            ->whereColumn('properties.id', 'contracts.property_id')
                            ->where(function ($propertyQuery) use ($userId): void {
                                $propertyQuery->where('properties.created_by', $userId)
                                    ->orWhere('properties.updated_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_units')
                            ->whereColumn('rental_units.contract_id', 'contracts.id')
                            ->where('rental_units.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('complaints')
                            ->whereColumn('complaints.property_id', 'contracts.property_id')
                            ->where('complaints.assigned_to', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('collaborations')
                            ->whereColumn('collaborations.property_id', 'contracts.property_id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    });
            });

            return;
        }

        // TODO: employees and portal users need explicit supported contract relationships before listing contracts.
        $query->whereRaw('1 = 0');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function tenantData(array $data, mixed $user): array
    {
        $agencyId = app(TenantContext::class)->agencyId();

        if ($agencyId === null && $user instanceof User) {
            $agencyId = $user->agency_id === null ? null : (int) $user->agency_id;
        }

        if ($agencyId !== null) {
            $data['agency_id'] = $agencyId;
        }

        return $data;
    }
}
