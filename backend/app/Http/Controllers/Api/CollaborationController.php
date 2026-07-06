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
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollaborationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CollaborationService $collaborationService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Collaboration::class);

        $query = Collaboration::query()
            ->with(['property', 'client', 'requestingAgent', 'ownerAgent', 'creator'])
            ->latest();

        $this->applyIndexAuthorization($query, $request);

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
        $this->authorize('create', Collaboration::class);

        $user = $request->user();
        $collaboration = $this->collaborationService->createCollaboration(
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return (new CollaborationResource($this->freshCollaboration($collaboration)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Collaboration $collaboration): CollaborationResource
    {
        $this->authorize('view', $collaboration);

        return new CollaborationResource($this->freshCollaboration($collaboration));
    }

    public function update(UpdateCollaborationRequest $request, Collaboration $collaboration): CollaborationResource
    {
        $this->authorize('update', $collaboration);

        $user = $request->user();
        $collaboration = $this->collaborationService->updateCollaboration(
            $collaboration,
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return new CollaborationResource($this->freshCollaboration($collaboration));
    }

    public function destroy(Collaboration $collaboration): JsonResponse
    {
        $this->authorize('delete', $collaboration);

        $collaboration->delete();

        return response()->json([
            'message' => 'Collaboration deleted successfully.',
        ]);
    }

    public function accept(Collaboration $collaboration): CollaborationResource
    {
        $this->authorize('accept', $collaboration);

        $user = request()->user();

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->acceptCollaboration($collaboration, $user instanceof User ? $user : null)
        ));
    }

    public function reject(Collaboration $collaboration): CollaborationResource
    {
        $this->authorize('reject', $collaboration);

        $user = request()->user();
        $reason = (string) request()->input('reason', request()->input('rejection_reason', 'Rejected'));

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->rejectCollaboration($collaboration, $reason, $user instanceof User ? $user : null)
        ));
    }

    public function cancel(Collaboration $collaboration): CollaborationResource
    {
        $this->authorize('cancel', $collaboration);

        $user = request()->user();

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->cancelCollaboration($collaboration, $user instanceof User ? $user : null)
        ));
    }

    public function complete(Collaboration $collaboration): CollaborationResource
    {
        $this->authorize('complete', $collaboration);

        $user = request()->user();

        return new CollaborationResource($this->freshCollaboration(
            $this->collaborationService->completeCollaboration($collaboration, $user instanceof User ? $user : null)
        ));
    }

    public function addMessage(Collaboration $collaboration): JsonResponse
    {
        $this->authorize('addMessage', $collaboration);

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
        $this->authorize('addDocument', $collaboration);

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
        $this->authorize('scheduleVisit', $collaboration);

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
        $this->authorize('submitOffer', $collaboration);

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
                $builder->where('requesting_agent_id', $userId)
                    ->orWhere('owner_agent_id', $userId)
                    ->orWhere('created_by', $userId);
            });

            return;
        }

        // TODO: employees and portal users need explicit collaboration participation links before listing collaborations.
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
