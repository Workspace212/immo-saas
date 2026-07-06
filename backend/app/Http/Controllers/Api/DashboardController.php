<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDashboardSnapshotRequest;
use App\Http\Requests\UpdateDashboardSnapshotRequest;
use App\Http\Resources\DashboardSnapshotResource;
use App\Models\AppNotification;
use App\Models\DashboardFavoriteFilter;
use App\Models\DashboardLayout;
use App\Models\DashboardSnapshot;
use App\Models\DashboardWidget;
use App\Models\User;
use App\Services\DashboardService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function overview(Request $request): JsonResponse
    {
        $this->authorizeDashboardView($request, requireAgencyWide: true);

        $user = $this->currentUser($request);
        $dashboard = $this->dashboardService->getDashboard($user);
        $statistics = $dashboard['statistics'] ?? [];
        $charts = $dashboard['charts'] ?? [];
        $alerts = $dashboard['alerts'] ?? [];

        return response()->json([
            'data' => [
                'kpis' => $statistics,
                'sales_statistics' => $charts['sales'] ?? [],
                'rentals_statistics' => $charts['rentals'] ?? [],
                'occupancy_rate' => $this->value($statistics, 'occupancy_rate', $charts['occupancy'] ?? []),
                'commissions' => $statistics['commissions'] ?? 0,
                'revenues' => $statistics['monthly_income'] ?? 0,
                'expenses' => $statistics['monthly_expenses'] ?? 0,
                'complaints_summary' => [
                    'total' => $statistics['complaints'] ?? 0,
                    'open' => $statistics['open_complaints'] ?? 0,
                    'chart' => $charts['complaints'] ?? [],
                ],
                'appointments_summary' => [
                    'today' => $statistics['appointments_today'] ?? 0,
                    'agenda' => $dashboard['agenda'] ?? [],
                ],
                'agent_activity' => $charts['agent_performance'] ?? [],
                'reminders' => $alerts,
                'upcoming_expirations' => [
                    'contracts' => $statistics['expiring_contracts'] ?? 0,
                ],
                'property_availability' => [
                    'available' => $statistics['available_properties'] ?? 0,
                    'occupied' => $statistics['occupied_properties'] ?? 0,
                    'total' => $statistics['properties'] ?? 0,
                ],
                'latest_notifications' => $dashboard['notifications'] ?? [],
                'dashboard' => $dashboard,
            ],
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $this->authorizeDashboardView($request, requireAgencyWide: true);

        $agencyId = $this->agencyId($request);

        return response()->json([
            'data' => $this->dashboardService->generateStatistics($agencyId),
        ]);
    }

    public function charts(Request $request): JsonResponse
    {
        $this->authorizeDashboardView($request, requireAgencyWide: true);

        $agencyId = $this->agencyId($request);

        return response()->json([
            'data' => $this->dashboardService->generateCharts($agencyId),
        ]);
    }

    public function agenda(Request $request): JsonResponse
    {
        $this->authorizeDashboardView($request, requireAgencyWide: true);

        $dashboard = $this->dashboardService->getDashboard($this->currentUser($request));

        return response()->json([
            'data' => $dashboard['agenda'] ?? [],
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $this->authorizeDashboardView($request);

        $user = $this->currentUser($request);

        return response()->json([
            'data' => AppNotification::query()
                ->where('user_id', $user->getKey())
                ->latest()
                ->limit((int) $request->integer('limit', 15))
                ->get(),
        ]);
    }

    public function favorites(Request $request): JsonResponse
    {
        $this->authorizeDashboardView($request);

        $user = $this->currentUser($request);
        $agencyId = $this->agencyId($request);

        return response()->json([
            'data' => DashboardFavoriteFilter::query()
                ->where('user_id', $user->getKey())
                ->where(function ($query) use ($agencyId): void {
                    $query->where('agency_id', $agencyId)
                        ->orWhereNull('agency_id');
                })
                ->orderBy('display_order')
                ->latest()
                ->get(),
        ]);
    }

    public function layouts(Request $request): JsonResponse
    {
        $this->authorizeDashboardView($request);

        $user = $this->currentUser($request);
        $agencyId = $this->agencyId($request);

        return response()->json([
            'data' => DashboardLayout::query()
                ->where(function ($query) use ($user): void {
                    $query->where('user_id', $user->getKey())
                        ->orWhere('is_shared', true);
                })
                ->where(function ($query) use ($agencyId): void {
                    $query->where('agency_id', $agencyId)
                        ->orWhereNull('agency_id');
                })
                ->orderByDesc('is_default')
                ->latest()
                ->get(),
        ]);
    }

    public function snapshots(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', DashboardSnapshot::class);

        $user = $this->currentUser($request);

        return DashboardSnapshotResource::collection(
            DashboardSnapshot::query()
                ->with('creator')
                ->where('agency_id', $this->agencyId($request))
                ->where(function ($query) use ($user): void {
                    $query->where('created_by', $user->getKey())
                        ->orWhereNull('created_by');
                })
                ->latest('snapshot_date')
                ->latest()
                ->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function storeSnapshot(StoreDashboardSnapshotRequest $request): JsonResponse
    {
        Gate::authorize('create', DashboardSnapshot::class);

        $snapshot = $this->dashboardService->saveSnapshot(
            $this->currentUser($request),
            $this->snapshotPayload($request->validated(), request: $request)
        );

        return (new DashboardSnapshotResource($snapshot->load('creator')))
            ->response()
            ->setStatusCode(201);
    }

    public function updateSnapshot(UpdateDashboardSnapshotRequest $request, DashboardSnapshot $snapshot): DashboardSnapshotResource
    {
        Gate::authorize('update', $snapshot);

        $updated = DB::transaction(function () use ($request, $snapshot): DashboardSnapshot {
            // TODO: Add authorization and immutable snapshot retention rules.
            $snapshot->fill($this->snapshotPayload($request->validated(), $snapshot, $request));
            $snapshot->save();

            return $snapshot->refresh();
        });

        return new DashboardSnapshotResource($updated->load('creator'));
    }

    public function deleteSnapshot(DashboardSnapshot $snapshot): JsonResponse
    {
        Gate::authorize('delete', $snapshot);

        DB::transaction(function () use ($snapshot): void {
            // TODO: Prevent deletion of audit snapshots generated by scheduled reporting.
            $snapshot->delete();
        });

        return response()->json(null, 204);
    }

    private function currentUser(Request $request): User
    {
        $user = $request->user();

        abort_if(! $user instanceof User, 401, 'Unauthenticated.');

        return $user;
    }

    private function agencyId(Request $request): int
    {
        $agencyId = app(TenantContext::class)->agencyId();
        $user = $this->currentUser($request);

        if ($agencyId === null) {
            $agencyId = $user->agency_id === null ? null : (int) $user->agency_id;
        }

        abort_if($agencyId === null, 403, 'Tenant agency is required.');

        return $agencyId;
    }

    private function authorizeDashboardView(Request $request, bool $requireAgencyWide = false): void
    {
        Gate::authorize('viewAny', DashboardWidget::class);

        if (! $requireAgencyWide) {
            return;
        }

        $user = $this->currentUser($request);

        // TODO: DashboardService currently builds agency-wide metrics; add role-aware service scoping before enabling agents/employees.
        abort_unless($user->hasAnyRole(['manager', 'assistant']), 403, 'Dashboard metrics require agency-wide dashboard access.');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function snapshotPayload(array $data, ?DashboardSnapshot $snapshot = null, ?Request $request = null): array
    {
        $metadata = $snapshot?->metadata ?? [];
        $metadata = is_array($metadata) ? $metadata : [];

        foreach (['snapshot_name', 'filters', 'widgets'] as $key) {
            if (array_key_exists($key, $data)) {
                $metadata[$key] = $data[$key];
            }
        }

        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $metadata = array_merge($metadata, $data['metadata']);
        }

        $payload = array_filter([
            'snapshot_key' => $data['snapshot_name'] ?? null,
            'period_type' => $data['period'] ?? null,
            'snapshot_date' => $data['snapshot_date'] ?? null,
            'metric_values' => $data['metrics'] ?? null,
            'comparison_values' => $data['comparison'] ?? null,
            'metadata' => $metadata,
        ], static fn (mixed $value): bool => $value !== null);

        if ($request !== null) {
            $payload['agency_id'] = $this->agencyId($request);
        }

        return $payload;
    }

    private function value(array $source, string $key, mixed $fallback = null): mixed
    {
        return $source[$key] ?? $fallback;
    }
}
