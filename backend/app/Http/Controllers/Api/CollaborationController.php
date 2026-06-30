<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollaborationRequest;
use App\Http\Requests\UpdateCollaborationRequest;
use App\Http\Resources\CollaborationResource;
use App\Models\Collaboration;
use App\Models\User;
use App\Services\CollaborationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollaborationController extends Controller
{
    public function __construct(private readonly CollaborationService $collaborationService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Collaboration::query()
            ->with(['property', 'client', 'requestingAgent', 'ownerAgent', 'creator'])
            ->latest();

        foreach ([
            'status',
            'collaboration_type',
            'property_id',
            'client_id',
            'requesting_agent_id',
            'owner_agent_id',
            'created_by',
        ] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->date('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->date('end_date'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('collaboration_number', 'like', $keyword)
                    ->orWhere('collaboration_type', 'like', $keyword)
                    ->orWhere('status', 'like', $keyword)
                    ->orWhere('request_message', 'like', $keyword)
                    ->orWhere('rejection_reason', 'like', $keyword);
            });
        }

        return CollaborationResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreCollaborationRequest $request): JsonResponse
    {
        $user = $request->user();
        $collaboration = $this->collaborationService->createCollaboration(
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return (new CollaborationResource($this->freshCollaboration($collaboration)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Collaboration $collaboration): CollaborationResource
    {
        return new CollaborationResource($this->freshCollaboration($collaboration));
    }

    public function update(UpdateCollaborationRequest $request, Collaboration $collaboration): CollaborationResource
    {
        $user = $request->user();
        $collaboration = $this->collaborationService->updateCollaboration(
            $collaboration,
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return new CollaborationResource($this->freshCollaboration($collaboration));
    }

    public function destroy(Collaboration $collaboration): JsonResponse
    {
        $collaboration->delete();

        return response()->json([
            'message' => 'Collaboration deleted successfully.',
        ]);
    }

    public function accept(Collaboration $collaboration): CollaborationResource
    {
        $user = request()->user();

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->acceptCollaboration($collaboration, $user instanceof User ? $user : null)
        ));
    }

    public function reject(Collaboration $collaboration): CollaborationResource
    {
        $user = request()->user();
        $reason = (string) request()->input('reason', request()->input('rejection_reason', 'Rejected'));

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->rejectCollaboration($collaboration, $reason, $user instanceof User ? $user : null)
        ));
    }

    public function cancel(Collaboration $collaboration): CollaborationResource
    {
        $user = request()->user();

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->cancelCollaboration($collaboration, $user instanceof User ? $user : null)
        ));
    }

    public function complete(Collaboration $collaboration): CollaborationResource
    {
        $user = request()->user();

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->completeCollaboration($collaboration, $user instanceof User ? $user : null)
        ));
    }

    public function addMessage(Collaboration $collaboration): JsonResponse
    {
        $user = request()->user();
        $message = $this->collaborationService->addMessage(
            $collaboration,
            (string) request()->input('message'),
            $user instanceof User ? $user : null,
            (bool) request()->boolean('is_internal', true),
        );

        return response()->json(['data' => $message], 201);
    }

    public function addDocument(Collaboration $collaboration): JsonResponse
    {
        $user = request()->user();
        $document = $this->collaborationService->addDocument(
            $collaboration,
            request()->only(['document_type', 'file_path', 'original_name', 'notes']),
            $user instanceof User ? $user : null,
        );

        return response()->json(['data' => $document], 201);
    }

    public function scheduleVisit(Collaboration $collaboration): JsonResponse
    {
        $user = request()->user();
        $visit = $this->collaborationService->scheduleVisit(
            $collaboration,
            request()->only(['property_id', 'client_id', 'visit_date', 'status', 'feedback', 'result']),
            $user instanceof User ? $user : null,
        );

        return response()->json(['data' => $visit], 201);
    }

    public function submitOffer(Collaboration $collaboration): JsonResponse
    {
        $user = request()->user();
        $offer = $this->collaborationService->submitOffer(
            $collaboration,
            request()->only(['property_id', 'client_id', 'offer_number', 'amount', 'currency', 'status', 'submitted_at', 'notes']),
            $user instanceof User ? $user : null,
        );

        return response()->json(['data' => $offer], 201);
    }

    private function freshCollaboration(Collaboration $collaboration): Collaboration
    {
        return $collaboration->refresh()->load(['property', 'client', 'requestingAgent', 'ownerAgent', 'creator']);
    }
}
