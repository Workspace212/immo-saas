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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RentalUnitController extends Controller
{
    public function __construct(private readonly RentalService $rentalService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RentalUnit::query()
            ->with(['property', 'contract', 'creator', 'assignedAgent'])
            ->latest();

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
        $user = $request->user();
        $rentalUnit = $this->rentalService->createRental(
            $this->serviceData($request->validated()),
            $user instanceof User ? $user : null,
        );

        return (new RentalUnitResource($this->freshRentalUnit($rentalUnit)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RentalUnit $rentalUnit): RentalUnitResource
    {
        return new RentalUnitResource($this->freshRentalUnit($rentalUnit));
    }

    public function update(UpdateRentalUnitRequest $request, RentalUnit $rentalUnit): RentalUnitResource
    {
        $user = $request->user();
        $rentalUnit = $this->rentalService->updateRental(
            $rentalUnit,
            $this->serviceData($request->validated()),
            $user instanceof User ? $user : null,
        );

        return new RentalUnitResource($this->freshRentalUnit($rentalUnit));
    }

    public function destroy(RentalUnit $rentalUnit): JsonResponse
    {
        $rentalUnit->delete();

        return response()->json([
            'message' => 'Rental unit deleted successfully.',
        ]);
    }

    public function activate(RentalUnit $rentalUnit): RentalUnitResource
    {
        return new RentalUnitResource($this->freshRentalUnit(
            $this->rentalService->activateRental($rentalUnit)
        ));
    }

    public function end(RentalUnit $rentalUnit): RentalUnitResource
    {
        return new RentalUnitResource($this->freshRentalUnit(
            $this->rentalService->endRental($rentalUnit)
        ));
    }

    public function cancel(RentalUnit $rentalUnit): RentalUnitResource
    {
        return new RentalUnitResource($this->freshRentalUnit(
            $this->rentalService->cancelRental($rentalUnit)
        ));
    }

    public function renew(RentalUnit $rentalUnit): JsonResponse
    {
        $renewedRental = $this->rentalService->renewRental($rentalUnit, []);

        return (new RentalUnitResource($this->freshRentalUnit($renewedRental)))
            ->response()
            ->setStatusCode(201);
    }

    private function freshRentalUnit(RentalUnit $rentalUnit): RentalUnit
    {
        return $rentalUnit->refresh()->load(['property', 'contract', 'creator', 'assignedAgent']);
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
