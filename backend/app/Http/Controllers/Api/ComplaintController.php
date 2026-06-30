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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    public function __construct(private readonly ComplaintService $complaintService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Complaint::query()
            ->with(['property', 'client', 'creator', 'assignee'])
            ->latest();

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
        $user = $request->user();
        $complaint = $this->complaintService->createComplaint(
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return (new ComplaintResource($this->freshComplaint($complaint)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Complaint $complaint): ComplaintResource
    {
        return new ComplaintResource($this->freshComplaint($complaint));
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): ComplaintResource
    {
        $user = $request->user();
        $complaint = $this->complaintService->updateComplaint(
            $complaint,
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return new ComplaintResource($this->freshComplaint($complaint));
    }

    public function destroy(Complaint $complaint): JsonResponse
    {
        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted successfully.',
        ]);
    }

    public function assignToUser(Complaint $complaint): ComplaintResource
    {
        $assigneeId = request()->integer('assigned_to');
        $assignee = User::query()->findOrFail($assigneeId);

        return new ComplaintResource($this->freshComplaint(
            $this->complaintService->assignToUser($complaint, $assignee)
        ));
    }

    public function markSeen(Complaint $complaint): ComplaintResource
    {
        return new ComplaintResource($this->freshComplaint($this->complaintService->markSeen($complaint)));
    }

    public function markInProgress(Complaint $complaint): ComplaintResource
    {
        return new ComplaintResource($this->freshComplaint($this->complaintService->markInProgress($complaint)));
    }

    public function markWaitingProvider(Complaint $complaint): ComplaintResource
    {
        return new ComplaintResource($this->freshComplaint($this->complaintService->markWaitingProvider($complaint)));
    }

    public function resolve(Complaint $complaint): ComplaintResource
    {
        $user = request()->user();

        return new ComplaintResource($this->freshComplaint(
            $this->complaintService->resolveComplaint($complaint, $user instanceof User ? $user : null)
        ));
    }

    public function close(Complaint $complaint): ComplaintResource
    {
        $user = request()->user();

        return new ComplaintResource($this->freshComplaint(
            $this->complaintService->closeComplaint($complaint, $user instanceof User ? $user : null)
        ));
    }

    public function reopen(Complaint $complaint): ComplaintResource
    {
        return new ComplaintResource($this->freshComplaint($this->complaintService->reopenComplaint($complaint)));
    }

    private function freshComplaint(Complaint $complaint): Complaint
    {
        return $complaint->refresh()->load(['property', 'client', 'creator', 'assignee']);
    }
}
