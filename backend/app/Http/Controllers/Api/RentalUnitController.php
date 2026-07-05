<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentalUnitRequest;
use App\Http\Requests\UpdateRentalUnitRequest;
use App\Http\Resources\RentalUnitResource;
use App\Models\PropertyOwner;
use App\Models\RentalParty;
use App\Models\RentalUnit;
use App\Models\User;
use App\Services\RentalService;
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RentalUnitController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly RentalService $rentalService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', RentalUnit::class);

        $query = RentalUnit::query()
            ->with(['property', 'contract', 'creator', 'assignedAgent'])
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        foreach (['status', 'property_id', 'contract_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('tenant_id')) {
            $query->whereIn('id', RentalParty::query()
                ->select('rental_unit_id')
                ->where('client_id', $request->integer('tenant_id')));
        }

        if ($request->filled('owner_id')) {
            $query->whereIn('property_id', PropertyOwner::query()
                ->select('property_id')
                ->where('owner_id', $request->integer('owner_id')));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->date('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->date('end_date'));
        }

        if ($request->filled('city')) {
            $query->whereHas('property', function ($builder) use ($request): void {
                $builder->where('city', $request->string('city')->toString());
            });
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('rental_number', 'like', $keyword)
                    ->orWhere('status', 'like', $keyword)
                    ->orWhere('deposit_status', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword)
                    ->orWhereHas('property', function ($propertyQuery) use ($keyword): void {
                        $propertyQuery->where('reference', 'like', $keyword)
                            ->orWhere('title', 'like', $keyword)
                            ->orWhere('city', 'like', $keyword)
                            ->orWhere('address', 'like', $keyword);
                    });
            });
        }

        return RentalUnitResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreRentalUnitRequest $request): JsonResponse
    {
        $this->authorize('create', RentalUnit::class);

        $user = $request->user();
        $rentalUnit = $this->rentalService->createRental(
            $this->serviceData($this->tenantData($request->validated(), $user)),
            $user instanceof User ? $user : null,
        );

        return (new RentalUnitResource($this->freshRentalUnit($rentalUnit)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RentalUnit $rentalUnit): RentalUnitResource
    {
        $this->authorize('view', $rentalUnit);

        return new RentalUnitResource($this->freshRentalUnit($rentalUnit));
    }

    public function update(UpdateRentalUnitRequest $request, RentalUnit $rentalUnit): RentalUnitResource
    {
        $this->authorize('update', $rentalUnit);

        $user = $request->user();
        $rentalUnit = $this->rentalService->updateRental(
            $rentalUnit,
            $this->serviceData($this->tenantData($request->validated(), $user)),
            $user instanceof User ? $user : null,
        );

        return new RentalUnitResource($this->freshRentalUnit($rentalUnit));
    }

    public function destroy(RentalUnit $rentalUnit): JsonResponse
    {
        $this->authorize('delete', $rentalUnit);

        $rentalUnit->delete();

        return response()->json([
            'message' => 'Rental unit deleted successfully.',
        ]);
    }

    public function activate(RentalUnit $rentalUnit): RentalUnitResource
    {
        $this->authorize('activate', $rentalUnit);

        return new RentalUnitResource($this->freshRentalUnit(
            $this->rentalService->activateRental($rentalUnit)
        ));
    }

    public function end(RentalUnit $rentalUnit): RentalUnitResource
    {
        $this->authorize('end', $rentalUnit);

        return new RentalUnitResource($this->freshRentalUnit(
            $this->rentalService->endRental($rentalUnit)
        ));
    }

    public function cancel(RentalUnit $rentalUnit): RentalUnitResource
    {
        $this->authorize('cancel', $rentalUnit);

        return new RentalUnitResource($this->freshRentalUnit(
            $this->rentalService->cancelRental($rentalUnit)
        ));
    }

    public function renew(RentalUnit $rentalUnit): JsonResponse
    {
        $this->authorize('renew', $rentalUnit);

        $renewedRental = $this->rentalService->renewRental($rentalUnit, []);

        return (new RentalUnitResource($this->freshRentalUnit($renewedRental)))
            ->response()
            ->setStatusCode(201);
    }

    private function freshRentalUnit(RentalUnit $rentalUnit): RentalUnit
    {
        return $rentalUnit->refresh()->load(['property', 'contract', 'creator', 'assignedAgent']);
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
                            ->from('contracts')
                            ->whereColumn('contracts.id', 'rental_units.contract_id')
                            ->where('contracts.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('properties')
                            ->whereColumn('properties.id', 'rental_units.property_id')
                            ->where(function ($propertyQuery) use ($userId): void {
                                $propertyQuery->where('properties.created_by', $userId)
                                    ->orWhere('properties.updated_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('complaints')
                            ->whereColumn('complaints.property_id', 'rental_units.property_id')
                            ->where('complaints.assigned_to', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('collaborations')
                            ->whereColumn('collaborations.property_id', 'rental_units.property_id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_parties')
                            ->join('client_property_requests', 'client_property_requests.client_id', '=', 'rental_parties.client_id')
                            ->whereColumn('rental_parties.rental_unit_id', 'rental_units.id')
                            ->where('client_property_requests.created_by', $userId);
                    });
            });

            return;
        }

        // TODO: employees and portal users need explicit supported rental relationships before listing rentals.
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

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function serviceData(array $data): array
    {
        if (array_key_exists('payment_schedules', $data)) {
            $data['schedules'] = $data['payment_schedules'];
            unset($data['payment_schedules']);
        }

        unset($data['documents']);

        return $data;
    }
}
