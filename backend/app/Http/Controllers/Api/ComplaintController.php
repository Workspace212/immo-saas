<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\ComplaintProvider;
use App\Models\User;
use App\Services\ComplaintService;
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ComplaintService $complaintService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Complaint::class);

        $query = Complaint::query()
            ->with(['property', 'client', 'creator', 'assignee'])
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        foreach (['status', 'priority', 'complaint_type', 'property_id', 'client_id', 'assigned_to', 'created_by'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('intervention_date')) {
            $date = $request->date('intervention_date');
            $query->where(function ($builder) use ($date): void {
                $builder->whereDate('intervention_date', $date)
                    ->orWhereIn('id', ComplaintProvider::query()
                        ->select('complaint_id')
                        ->whereDate('intervention_date', $date));
            });
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('complaint_number', 'like', $keyword)
                    ->orWhere('complaint_type', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword)
                    ->orWhere('provider_name', 'like', $keyword)
                    ->orWhere('provider_phone', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return ComplaintResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $this->authorize('create', Complaint::class);

        $user = $request->user();
        $complaint = $this->complaintService->createComplaint(
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return (new ComplaintResource($this->freshComplaint($complaint)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Complaint $complaint): ComplaintResource
    {
        $this->authorize('view', $complaint);

        return new ComplaintResource($this->freshComplaint($complaint));
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): ComplaintResource
    {
        $this->authorize('update', $complaint);

        $user = $request->user();
        $complaint = $this->complaintService->updateComplaint(
            $complaint,
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return new ComplaintResource($this->freshComplaint($complaint));
    }

    public function destroy(Complaint $complaint): JsonResponse
    {
        $this->authorize('delete', $complaint);

        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted successfully.',
        ]);
    }

    public function assignToUser(Complaint $complaint): ComplaintResource
    {
        $this->authorize('assignToUser', $complaint);

        $assigneeId = request()->integer('assigned_to');
        $assignee = User::query()->findOrFail($assigneeId);

        return new ComplaintResource($this->freshComplaint(
            $this->complaintService->assignToUser($complaint, $assignee)
        ));
    }

    public function markSeen(Complaint $complaint): ComplaintResource
    {
        $this->authorize('markSeen', $complaint);

        return new ComplaintResource($this->freshComplaint($this->complaintService->markSeen($complaint)));
    }

    public function markInProgress(Complaint $complaint): ComplaintResource
    {
        $this->authorize('markInProgress', $complaint);

        return new ComplaintResource($this->freshComplaint($this->complaintService->markInProgress($complaint)));
    }

    public function markWaitingProvider(Complaint $complaint): ComplaintResource
    {
        $this->authorize('markWaitingProvider', $complaint);

        return new ComplaintResource($this->freshComplaint($this->complaintService->markWaitingProvider($complaint)));
    }

    public function resolve(Complaint $complaint): ComplaintResource
    {
        $this->authorize('resolve', $complaint);

        $user = request()->user();

        return new ComplaintResource($this->freshComplaint(
            $this->complaintService->resolveComplaint($complaint, $user instanceof User ? $user : null)
        ));
    }

    public function close(Complaint $complaint): ComplaintResource
    {
        $this->authorize('close', $complaint);

        $user = request()->user();

        return new ComplaintResource($this->freshComplaint(
            $this->complaintService->closeComplaint($complaint, $user instanceof User ? $user : null)
        ));
    }

    public function reopen(Complaint $complaint): ComplaintResource
    {
        $this->authorize('reopen', $complaint);

        return new ComplaintResource($this->freshComplaint($this->complaintService->reopenComplaint($complaint)));
    }

    private function freshComplaint(Complaint $complaint): Complaint
    {
        return $complaint->refresh()->load(['property', 'client', 'creator', 'assignee']);
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
                $builder->where('assigned_to', $userId)
                    ->orWhere('created_by', $userId)
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('properties')
                            ->whereColumn('properties.id', 'complaints.property_id')
                            ->where(function ($propertyQuery) use ($userId): void {
                                $propertyQuery->where('properties.created_by', $userId)
                                    ->orWhere('properties.updated_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->whereColumn('contracts.property_id', 'complaints.property_id')
                            ->where('contracts.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_units')
                            ->whereColumn('rental_units.property_id', 'complaints.property_id')
                            ->where('rental_units.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('collaborations')
                            ->whereColumn('collaborations.property_id', 'complaints.property_id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('appointments')
                            ->whereColumn('appointments.complaint_id', 'complaints.id')
                            ->where('appointments.created_by', $userId);
                    });
            });

            return;
        }

        // TODO: employees and portal users need explicit supported complaint relationships before listing complaints.
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
