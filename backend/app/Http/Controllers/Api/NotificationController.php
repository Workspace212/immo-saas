<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Http\Resources\AppNotificationResource;
use App\Models\AppNotification;
use App\Models\NotificationQueue;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AppNotification::class);

        $query = AppNotification::query()
            ->with(['creator', 'user'])
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        foreach (['status', 'priority', 'type', 'created_by'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('channel')) {
            $query->where('data->channel', $request->input('channel'));
        }

        if ($request->filled('recipient')) {
            $recipient = $request->string('recipient')->toString();

            $query->where(function ($builder) use ($recipient): void {
                if (is_numeric($recipient)) {
                    $builder->where('user_id', (int) $recipient);
                }

                $builder->orWhereHas('user', function ($userQuery) use ($recipient): void {
                    $like = '%' . $recipient . '%';
                    $userQuery->where('email', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('notification_number', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword)
                    ->orWhere('message', 'like', $keyword)
                    ->orWhere('type', 'like', $keyword);
            });
        }

        return AppNotificationResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $this->authorize('create', AppNotification::class);

        $data = $this->tenantData($this->notificationData($request->validated()), $request->user());
        $recipient = $this->recipientUser($data, $request);

        $notification = $this->notificationService->notifyUser($recipient, $data, $request->user());

        if (($data['scheduled_at'] ?? null) !== null || ($data['channel'] ?? 'internal') !== 'internal') {
            $this->notificationService->queueNotification($this->queueData($notification, $data));
        }

        return (new AppNotificationResource($notification->load(['creator', 'user'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(AppNotification $notification): AppNotificationResource
    {
        $this->authorize('view', $notification);

        return new AppNotificationResource($notification->load(['creator', 'user']));
    }

    public function update(UpdateNotificationRequest $request, AppNotification $notification): AppNotificationResource
    {
        $this->authorize('update', $notification);

        $updated = DB::transaction(function () use ($request, $notification): AppNotification {
            // TODO: Move mutable notification workflow into NotificationService when status rules are finalized.
            $notification->fill($this->appNotificationData($this->tenantData($this->notificationData($request->validated()), $request->user())));
            $notification->save();

            return $notification->refresh();
        });

        return new AppNotificationResource($updated->load(['creator', 'user']));
    }

    public function destroy(AppNotification $notification): JsonResponse
    {
        $this->authorize('delete', $notification);

        DB::transaction(function () use ($notification): void {
            // TODO: Enforce retention rules for audit-critical notifications.
            $notification->delete();
        });

        return response()->json(null, 204);
    }

    public function markAsRead(AppNotification $notification): AppNotificationResource
    {
        $this->authorize('markAsRead', $notification);

        return new AppNotificationResource(
            $this->notificationService->markAsRead($notification)->load(['creator', 'user'])
        );
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->authorize('markAllAsRead', AppNotification::class);

        $user = $request->user();

        abort_if(! $user instanceof User, 401, 'Unauthenticated.');

        $notifications = AppNotification::query()
            ->where('user_id', $user->getKey())
            ->whereNotIn('status', ['read', 'archived'])
            ->get();

        foreach ($notifications as $notification) {
            $this->notificationService->markAsRead($notification);
        }

        return response()->json([
            'data' => [
                'marked_read' => $notifications->count(),
            ],
        ]);
    }

    public function archive(AppNotification $notification): AppNotificationResource
    {
        $this->authorize('archive', $notification);

        return new AppNotificationResource(
            $this->notificationService->archive($notification)->load(['creator', 'user'])
        );
    }

    public function restore(AppNotification $notification): AppNotificationResource
    {
        $this->authorize('restore', $notification);

        $restored = $this->notificationService->markAsUnread($notification);

        return new AppNotificationResource($restored->load(['creator', 'user']));
    }

    public function sendNow(AppNotification $notification): JsonResponse
    {
        $this->authorize('sendNow', $notification);

        $queue = $this->notificationService->queueNotification($this->queueData($notification, is_array($notification->data) ? $notification->data : []));
        $sent = $this->notificationService->sendQueued($queue);

        return response()->json([
            'data' => $sent,
        ], 201);
    }

    public function queue(AppNotification $notification): JsonResponse
    {
        $this->authorize('queue', $notification);

        $queue = $this->notificationService->queueNotification($this->queueData($notification, is_array($notification->data) ? $notification->data : []));

        return response()->json([
            'data' => $queue,
        ], 201);
    }

    private function applyIndexAuthorization($query, Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401, 'Unauthenticated.');

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return;
        }

        // TODO: current AppNotificationPolicy denies agent/external viewAny; keep non-admin listings closed.
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
    private function notificationData(array $data): array
    {
        $payload = $data['payload'] ?? [];
        $metadata = $data['data'] ?? [];
        $templateId = $data['notification_template_id'] ?? ($data['template']['id'] ?? null);

        if (($data['channel'] ?? null) !== null) {
            $metadata['channel'] = $data['channel'];
        }

        if ($templateId !== null) {
            $metadata['notification_template_id'] = $templateId;
        }

        if (($data['scheduled_at'] ?? null) !== null) {
            $metadata['scheduled_at'] = $data['scheduled_at'];
        }

        if ($payload !== []) {
            $metadata['payload'] = $payload;
        }

        return array_filter([
            'agency_id' => $data['agency_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'recipient' => $data['recipient'] ?? null,
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? ($data['body'] ?? null),
            'type' => $data['type'] ?? null,
            'priority' => $data['priority'] ?? null,
            'channel' => $data['channel'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'notification_template_id' => $templateId,
            'action_url' => $data['action_url'] ?? null,
            'related_type' => $data['related_type'] ?? null,
            'related_id' => $data['related_id'] ?? null,
            'data' => $metadata,
            'status' => $data['status'] ?? null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function recipientUser(array $data, Request $request): User
    {
        $user = isset($data['user_id']) ? User::query()->find($data['user_id']) : null;
        $recipient = $data['recipient'] ?? null;

        if (! $user instanceof User && is_numeric($recipient)) {
            $user = User::query()->find((int) $recipient);
        }

        if (! $user instanceof User && is_string($recipient)) {
            $user = User::query()->where('email', $recipient)->first();
        }

        $fallback = $request->user();

        abort_if(! $user instanceof User && ! $fallback instanceof User, 422, 'A valid notification recipient is required.');

        return $user instanceof User ? $user : $fallback;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function appNotificationData(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'agency_id',
            'user_id',
            'title',
            'message',
            'type',
            'priority',
            'action_url',
            'related_type',
            'related_id',
            'data',
            'status',
        ]));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function queueData(AppNotification $notification, array $data): array
    {
        return [
            'agency_id' => $notification->agency_id,
            'user_id' => $notification->user_id,
            'notification_template_id' => $data['notification_template_id'] ?? null,
            'channel' => $data['channel'] ?? 'internal',
            'recipient' => (string) ($data['recipient'] ?? $notification->user?->email ?? $notification->user_id),
            'subject' => $notification->title,
            'body' => $notification->message,
            'payload' => $data['payload'] ?? $notification->data ?? [],
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ];
    }
}
