<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Http\Resources\OwnerResource;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\Owner;
use App\Models\User;
use App\Services\OwnerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OwnerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly OwnerService $ownerService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Owner::class);

        $query = Owner::query()
            ->with('agency')
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        if ($request->filled('owner_type')) {
            $query->where('type', $request->input('owner_type'));
        }

        foreach (['city', 'country'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->has('is_active')) {
            $query->where('status', $request->boolean('is_active') ? 'active' : 'archived');
        }

        if ($request->filled('assigned_agent')) {
            $contractIds = Contract::query()
                ->where('assigned_agent_id', $request->integer('assigned_agent'))
                ->select('id');

            $query->whereIn('id', ContractParty::query()
                ->whereIn('contract_id', $contractIds)
                ->whereNotNull('owner_id')
                ->select('owner_id'));
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->date('created_at'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('full_name', 'like', $keyword)
                    ->orWhere('company_name', 'like', $keyword)
                    ->orWhere('cin_passport', 'like', $keyword)
                    ->orWhere('phone', 'like', $keyword)
                    ->orWhere('whatsapp', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword)
                    ->orWhere('address', 'like', $keyword)
                    ->orWhere('city', 'like', $keyword)
                    ->orWhere('country', 'like', $keyword)
                    ->orWhere('ice', 'like', $keyword)
                    ->orWhere('rc', 'like', $keyword)
                    ->orWhere('if_number', 'like', $keyword)
                    ->orWhere('patente', 'like', $keyword)
                    ->orWhere('representative_name', 'like', $keyword)
                    ->orWhere('bank_name', 'like', $keyword)
                    ->orWhere('rib_iban', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return OwnerResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreOwnerRequest $request): JsonResponse
    {
        $this->authorize('create', Owner::class);

        $owner = $this->ownerService->create($this->tenantData($request->validated(), $request->user()), $request->user());

        return (new OwnerResource($this->freshOwner($owner)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Owner $owner): OwnerResource
    {
        $this->authorize('view', $owner);

        return new OwnerResource($this->freshOwner($owner));
    }

    public function update(UpdateOwnerRequest $request, Owner $owner): OwnerResource
    {
        $this->authorize('update', $owner);

        $owner = $this->ownerService->update($owner, $this->tenantData($request->validated(), $request->user()), $request->user());

        return new OwnerResource($this->freshOwner($owner));
    }

    public function destroy(Owner $owner): JsonResponse
    {
        $this->authorize('delete', $owner);

        $this->ownerService->delete($owner);

        return response()->json([
            'message' => 'Owner deleted successfully.',
        ]);
    }

    public function archive(Owner $owner): OwnerResource
    {
        $this->authorize('archive', $owner);

        return new OwnerResource($this->freshOwner(
            $this->ownerService->archive($owner)
        ));
    }

    public function restore(Owner $owner): OwnerResource
    {
        $this->authorize('restore', $owner);

        return new OwnerResource($this->freshOwner(
            $this->ownerService->restore($owner)
        ));
    }

    private function freshOwner(Owner $owner): Owner
    {
        return $owner->refresh()->load('agency');
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

            $query->whereExists(function ($subquery) use ($userId): void {
                $subquery->selectRaw('1')
                    ->from('property_owners')
                    ->join('properties', 'properties.id', '=', 'property_owners.property_id')
                    ->whereColumn('property_owners.owner_id', 'owners.id')
                    ->where(function ($builder) use ($userId): void {
                        $builder->where('properties.created_by', $userId)
                            ->orWhere('properties.updated_by', $userId)
                            ->orWhereExists(function ($contractQuery) use ($userId): void {
                                $contractQuery->selectRaw('1')
                                    ->from('contracts')
                                    ->whereColumn('contracts.property_id', 'properties.id')
                                    ->where('contracts.assigned_agent_id', $userId);
                            })
                            ->orWhereExists(function ($rentalQuery) use ($userId): void {
                                $rentalQuery->selectRaw('1')
                                    ->from('rental_units')
                                    ->whereColumn('rental_units.property_id', 'properties.id')
                                    ->where('rental_units.assigned_agent_id', $userId);
                            })
                            ->orWhereExists(function ($complaintQuery) use ($userId): void {
                                $complaintQuery->selectRaw('1')
                                    ->from('complaints')
                                    ->whereColumn('complaints.property_id', 'properties.id')
                                    ->where('complaints.assigned_to', $userId);
                            })
                            ->orWhereExists(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->selectRaw('1')
                                    ->from('collaborations')
                                    ->whereColumn('collaborations.property_id', 'properties.id')
                                    ->where(function ($collaborationBuilder) use ($userId): void {
                                        $collaborationBuilder->where('collaborations.requesting_agent_id', $userId)
                                            ->orWhere('collaborations.owner_agent_id', $userId)
                                            ->orWhere('collaborations.created_by', $userId);
                                    });
                            });
                    });
            });

            return;
        }

        // TODO: employees and owner portal users need explicit user-owner links before listing owners.
        $query->whereRaw('1 = 0');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function tenantData(array $data, mixed $user): array
    {
        if ($user instanceof User) {
            $data['agency_id'] = $user->agency_id;
        }

        return $data;
    }
}
