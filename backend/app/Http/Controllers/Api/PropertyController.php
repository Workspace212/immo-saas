<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyActivity;
use App\Models\User;
use App\Services\PropertyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly PropertyService $propertyService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Property::class);

        $query = Property::query()
            ->with(['propertyType', 'creator', 'updater'])
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('city')) {
            $query->where('city', $request->string('city')->toString());
        }

        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->integer('property_type_id'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('reference', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword)
                    ->orWhere('city', 'like', $keyword)
                    ->orWhere('sector', 'like', $keyword)
                    ->orWhere('address', 'like', $keyword);
            });
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $activityQuery = PropertyActivity::query()->select('property_id');

            if ($request->filled('min_price')) {
                $activityQuery->where('price', '>=', $request->input('min_price'));
            }

            if ($request->filled('max_price')) {
                $activityQuery->where('price', '<=', $request->input('max_price'));
            }

            $query->whereIn('id', $activityQuery);
        }

        $properties = $query->paginate((int) $request->integer('per_page', 15));

        return PropertyResource::collection($properties);
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $this->authorize('create', Property::class);

        $user = $request->user();
        $data = $this->tenantData($request->validated(), $user);
        $property = $this->propertyService->createProperty(
            $data,
            $user instanceof User ? $user : null,
        );

        $this->syncAdditionalRelations($property, $data, $user instanceof User ? $user : null);

        return (new PropertyResource($this->freshProperty($property)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Property $property): PropertyResource
    {
        $this->authorize('view', $property);

        return new PropertyResource($this->freshProperty($property));
    }

    public function update(UpdatePropertyRequest $request, Property $property): PropertyResource
    {
        $this->authorize('update', $property);

        $user = $request->user();
        $data = $this->tenantData($request->validated(), $user);
        $property = $this->propertyService->updateProperty(
            $property,
            $data,
            $user instanceof User ? $user : null,
        );

        $this->syncAdditionalRelations($property, $data, $user instanceof User ? $user : null);

        return new PropertyResource($this->freshProperty($property));
    }

    public function destroy(Property $property): JsonResponse
    {
        $this->authorize('delete', $property);

        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully.',
        ]);
    }

    public function archive(Property $property): PropertyResource
    {
        $this->authorize('archive', $property);

        $user = request()->user();
        $property = $this->propertyService->archiveProperty($property, $user instanceof User ? $user : null);

        return new PropertyResource($this->freshProperty($property));
    }

    private function freshProperty(Property $property): Property
    {
        return $property->refresh()->load(['propertyType', 'creator', 'updater']);
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
                $builder->where('created_by', $userId)
                    ->orWhere('updated_by', $userId)
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->whereColumn('contracts.property_id', 'properties.id')
                            ->where('contracts.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_units')
                            ->whereColumn('rental_units.property_id', 'properties.id')
                            ->where('rental_units.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('complaints')
                            ->whereColumn('complaints.property_id', 'properties.id')
                            ->where('complaints.assigned_to', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('collaborations')
                            ->whereColumn('collaborations.property_id', 'properties.id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    });
            });

            return;
        }

        // TODO: employees and portal users need explicit ownership links before listing properties.
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

    /**
     * @param array<string, mixed> $data
     */
    private function syncAdditionalRelations(Property $property, array $data, ?User $user = null): void
    {
        foreach ($data['media'] ?? [] as $mediaData) {
            if (is_array($mediaData)) {
                $this->propertyService->addMedia($property, $mediaData, $user);
            }
        }

        foreach ($data['documents'] ?? [] as $documentData) {
            if (is_array($documentData)) {
                $this->propertyService->addDocument($property, $documentData, $user);
            }
        }

        if (array_key_exists('availabilities', $data) && is_array($data['availabilities'])) {
            foreach ($data['availabilities'] as $availabilityData) {
                if (is_array($availabilityData)) {
                    $this->propertyService->addAvailability($property, $availabilityData, $user);
                }
            }
        }
    }
}
