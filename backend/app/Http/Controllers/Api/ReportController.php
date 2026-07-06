<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\ScheduledReport;
use App\Models\User;
use App\Services\ReportService;
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Report::class);

        $query = Report::query()
            ->with('creator')
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        foreach (['report_type', 'category', 'created_by'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('is_scheduled')) {
            $scheduled = $request->boolean('is_scheduled');
            $query->{$scheduled ? 'whereExists' : 'whereNotExists'}(function ($subQuery): void {
                $subQuery->selectRaw('1')
                    ->from('scheduled_reports')
                    ->whereColumn('scheduled_reports.report_id', 'reports.id')
                    ->whereNull('scheduled_reports.deleted_at');
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
                $builder->where('report_number', 'like', $keyword)
                    ->orWhere('name', 'like', $keyword)
                    ->orWhere('report_type', 'like', $keyword)
                    ->orWhere('category', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return ReportResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $this->authorize('create', Report::class);

        $report = DB::transaction(function () use ($request): Report {
            // TODO: Move report template creation into ReportService when report lifecycle rules are finalized.
            return Report::query()->create($this->reportData($this->tenantData($request->validated(), $request->user()), [
                'created_by' => $request->user()?->getKey(),
                'report_number' => $this->reportService->generateReportNumber(),
            ]));
        });

        return (new ReportResource($report->load('creator')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Report $report): ReportResource
    {
        $this->authorize('view', $report);

        return new ReportResource($report->load('creator'));
    }

    public function update(UpdateReportRequest $request, Report $report): ReportResource
    {
        $this->authorize('update', $report);

        $updated = DB::transaction(function () use ($request, $report): Report {
            // TODO: Add versioning before allowing changes to shared or scheduled reports.
            $report->fill($this->reportData($this->tenantData($request->validated(), $request->user())));
            $report->save();

            return $report->refresh();
        });

        return new ReportResource($updated->load('creator'));
    }

    public function destroy(Report $report): JsonResponse
    {
        $this->authorize('delete', $report);

        DB::transaction(function () use ($report): void {
            // TODO: Prevent deletion when exports or audit-required schedules must be retained.
            $report->delete();
        });

        return response()->json(null, 204);
    }

    public function generate(Report $report): JsonResponse
    {
        $this->authorize('generate', $report);

        $from = $report->period_start instanceof Carbon ? $report->period_start : Carbon::now()->startOfMonth();
        $to = $report->period_end instanceof Carbon ? $report->period_end : Carbon::now()->endOfMonth();
        $agencyId = (int) $report->agency_id;

        $data = match ($report->report_type) {
            'financial' => $this->reportService->generateFinancialReport($agencyId, $from, $to),
            'sales', 'sale' => $this->reportService->generateSalesReport($agencyId, $from, $to),
            'rental', 'rentals' => $this->reportService->generateRentalReport($agencyId, $from, $to),
            'complaint', 'complaints' => $this->reportService->generateComplaintReport($agencyId, $from, $to),
            'agent', 'agent_performance' => $this->reportService->generateAgentPerformanceReport($agencyId, $from, $to),
            default => [
                'report' => new ReportResource($report->load('creator')),
                'period' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ],
                'data' => [],
                'todo' => 'TODO: Add generator for this report type.',
            ],
        };

        return response()->json(['data' => $data]);
    }

    public function exportPdf(Report $report): JsonResponse
    {
        $this->authorize('exportPdf', $report);

        return response()->json([
            'data' => $this->reportService->exportPdf($report),
        ], 201);
    }

    public function exportExcel(Report $report): JsonResponse
    {
        $this->authorize('exportExcel', $report);

        return response()->json([
            'data' => $this->reportService->exportExcel($report),
        ], 201);
    }

    public function exportCsv(Report $report): JsonResponse
    {
        $this->authorize('exportCsv', $report);

        return response()->json([
            'data' => $this->reportService->exportCsv($report),
        ], 201);
    }

    public function schedule(Report $report): JsonResponse
    {
        $this->authorize('schedule', $report);

        $payload = request()->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'send_time' => ['nullable', 'date_format:H:i'],
            'recipients' => ['nullable', 'array'],
            'email_enabled' => ['nullable', 'boolean'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'next_run_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        return response()->json([
            'data' => $this->reportService->scheduleReport(array_merge($payload, [
                'agency_id' => $report->agency_id,
                'report_id' => $report->getKey(),
                'name' => $payload['name'] ?? $report->name,
            ]), request()->user()),
        ], 201);
    }

    public function share(Report $report): JsonResponse
    {
        $this->authorize('share', $report);

        $payload = request()->validate([
            'shared_with_user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:255'],
            'can_view' => ['nullable', 'boolean'],
            'can_edit' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        return response()->json([
            'data' => $this->reportService->shareReport(array_merge($payload, [
                'agency_id' => $report->agency_id,
                'report_id' => $report->getKey(),
            ]), request()->user()),
        ], 201);
    }

    private function applyIndexAuthorization($query, Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401, 'Unauthenticated.');

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return;
        }

        if ($user->hasAnyRole(['agent', 'employee'])) {
            $userId = (int) $user->getKey();

            $query->where(function ($builder) use ($userId): void {
                $builder->where('created_by', $userId)
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('report_shares')
                            ->whereColumn('report_shares.report_id', 'reports.id')
                            ->where('report_shares.shared_with_user_id', $userId)
                            ->where('report_shares.can_view', true)
                            ->whereNull('report_shares.deleted_at')
                            ->where(function ($shareQuery): void {
                                $shareQuery->whereNull('report_shares.expires_at')
                                    ->orWhere('report_shares.expires_at', '>', now());
                            });
                    });
            });

            return;
        }

        // TODO: portal users need explicit report sharing/identity rules before listing reports.
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
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function reportData(array $data, array $extra = []): array
    {
        $parameters = $data['parameters'] ?? [];

        foreach (['columns', 'grouping', 'sorting', 'format', 'schedule', 'sharing'] as $key) {
            if (array_key_exists($key, $data)) {
                $parameters[$key] = $data[$key];
            }
        }

        return array_filter(array_merge([
            'agency_id' => $data['agency_id'] ?? null,
            'name' => $data['report_name'] ?? null,
            'report_type' => $data['report_type'] ?? null,
            'category' => $data['category'] ?? null,
            'parameters' => $parameters,
            'filters' => $data['filters'] ?? null,
            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'visibility' => $data['visibility'] ?? null,
            'is_favorite' => $data['is_favorite'] ?? null,
            'is_active' => $data['is_active'] ?? null,
            'notes' => $data['notes'] ?? null,
        ], $extra), static fn (mixed $value): bool => $value !== null);
    }
}
