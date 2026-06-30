<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DashboardFavoriteFilter;
use App\Models\DashboardLayout;
use App\Models\DashboardSnapshot;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardService
{
    public function __construct(
        private readonly FinancialService $financialService,
        private readonly PropertyService $propertyService,
        private readonly ContractService $contractService,
        private readonly ComplaintService $complaintService,
        private readonly ReportService $reportService,
    ) {
    }

    public function getDashboard(User $user): array
    {
        if ($user->hasRole('Manager')) {
            return $this->getManagerDashboard($user);
        }

        if ($user->hasRole('Agent')) {
            return $this->getAgentDashboard($user);
        }

        if ($user->hasRole('Assistant')) {
            return $this->getAssistantDashboard($user);
        }

        if ($user->hasRole('Owner')) {
            return $this->getOwnerDashboard($user);
        }

        if ($user->hasRole('Client')) {
            return $this->getClientDashboard($user);
        }

        return $this->buildDashboard($user, 'default');
    }

    public function getManagerDashboard(User $user): array
    {
        return $this->buildDashboard($user, 'manager');
    }

    public function getAgentDashboard(User $user): array
    {
        return $this->buildDashboard($user, 'agent');
    }

    public function getAssistantDashboard(User $user): array
    {
        return $this->buildDashboard($user, 'assistant');
    }

    public function getOwnerDashboard(User $user): array
    {
        return $this->buildDashboard($user, 'owner');
    }

    public function getClientDashboard(User $user): array
    {
        return $this->buildDashboard($user, 'client');
    }

    public function saveLayout(User $user, array $layout): DashboardLayout
    {
        return DB::transaction(function () use ($user, $layout): DashboardLayout {
            $layoutData = array_intersect_key($layout, array_flip([
                'agency_id',
                'user_id',
                'name',
                'slug',
                'is_default',
                'is_shared',
                'layout',
                'settings',
            ]));

            $layoutData['agency_id'] ??= $user->agency_id;
            $layoutData['user_id'] = $user->getKey();
            $layoutData['name'] ??= 'Default dashboard';
            $layoutData['slug'] ??= Str::slug((string) $layoutData['name']);
            $layoutData['is_default'] ??= false;
            $layoutData['is_shared'] ??= false;

            // TODO: Enforce a single default dashboard layout per user.
            return DashboardLayout::query()->updateOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'slug' => $layoutData['slug'],
                ],
                $layoutData
            );
        });
    }

    public function saveFavoriteFilter(User $user, array $filter): DashboardFavoriteFilter
    {
        return DB::transaction(function () use ($user, $filter): DashboardFavoriteFilter {
            $filterData = array_intersect_key($filter, array_flip([
                'agency_id',
                'user_id',
                'module',
                'name',
                'search_query',
                'filters',
                'is_favorite',
                'is_shared',
                'display_order',
            ]));

            $filterData['agency_id'] ??= $user->agency_id;
            $filterData['user_id'] = $user->getKey();
            $filterData['module'] ??= 'dashboard';
            $filterData['name'] ??= 'Favorite filter';
            $filterData['is_favorite'] ??= true;
            $filterData['is_shared'] ??= false;
            $filterData['display_order'] ??= 0;

            // TODO: Validate module names against enabled dashboard modules.
            return DashboardFavoriteFilter::query()->create($filterData);
        });
    }

    public function saveSnapshot(User $user, array $snapshot): DashboardSnapshot
    {
        return DB::transaction(function () use ($user, $snapshot): DashboardSnapshot {
            $snapshotData = array_intersect_key($snapshot, array_flip([
                'agency_id',
                'created_by',
                'snapshot_key',
                'period_type',
                'snapshot_date',
                'metric_values',
                'comparison_values',
                'metadata',
            ]));

            $snapshotData['agency_id'] ??= $user->agency_id;
            $snapshotData['created_by'] = $user->getKey();
            $snapshotData['snapshot_key'] ??= sprintf('dashboard-%s', Carbon::now()->format('YmdHis'));
            $snapshotData['period_type'] ??= 'daily';
            $snapshotData['snapshot_date'] ??= Carbon::now()->toDateString();
            $snapshotData['metric_values'] ??= $this->generateStatistics((int) $snapshotData['agency_id']);
            $snapshotData['comparison_values'] ??= [];
            $snapshotData['metadata'] ??= [];

            // TODO: Add retention and uniqueness rules for automated dashboard snapshots.
            return DashboardSnapshot::query()->create($snapshotData);
        });
    }

    public function restoreSnapshot(DashboardSnapshot $snapshot): array
    {
        return [
            'snapshot_key' => $snapshot->snapshot_key,
            'period_type' => $snapshot->period_type,
            'snapshot_date' => $snapshot->snapshot_date?->toDateString(),
            'statistics' => $snapshot->metric_values ?? [],
            'comparison' => $snapshot->comparison_values ?? [],
            'metadata' => $snapshot->metadata ?? [],
        ];
    }

    public function generateStatistics(int $agencyId): array
    {
        $monthFrom = Carbon::now()->startOfMonth();
        $monthTo = Carbon::now()->endOfMonth();
        $financial = $this->financialService->generateFinancialSummary($agencyId, $monthFrom, $monthTo);

        return [
            'properties' => $this->countRows('properties', $agencyId),
            'available_properties' => $this->countRows('properties', $agencyId, ['status' => 'available']),
            'occupied_properties' => $this->countRows('properties', $agencyId, ['status' => 'occupied']),
            'contracts' => $this->countRows('contracts', $agencyId),
            'expiring_contracts' => $this->countExpiringContracts($agencyId),
            'complaints' => $this->countRows('complaints', $agencyId),
            'open_complaints' => $this->countOpenComplaints($agencyId),
            'monthly_income' => $financial['income'] ?? 0.0,
            'monthly_expenses' => $financial['expenses'] ?? 0.0,
            'profit' => $financial['profit'] ?? 0.0,
            'commissions' => $financial['commissions'] ?? 0.0,
            'owner_payments' => $financial['owner_payments'] ?? 0.0,
            'appointments_today' => $this->countToday('appointments', $agencyId, 'start_at'),
            'visits_today' => $this->countTodayVisits($agencyId),
        ];
    }

    public function generateCharts(int $agencyId): array
    {
        // TODO: Replace placeholder chart payloads with persisted analytics snapshots when available.
        return [
            'monthly_revenue' => $this->monthlySeries($agencyId, 'financial_transactions', 'transaction_date', 'amount', [
                'transaction_type' => 'credit',
            ]),
            'monthly_expenses' => $this->monthlySeries($agencyId, 'expenses', 'expense_date', 'amount_ttc'),
            'complaints' => $this->statusDistribution($agencyId, 'complaints'),
            'occupancy' => [],
            'agent_performance' => [],
            'sales' => [],
            'rentals' => [],
        ];
    }

    public function generateAlerts(User $user): array
    {
        $agencyId = (int) $user->agency_id;

        return [
            'contracts_expiring' => $this->countExpiringContracts($agencyId),
            'late_payments' => $this->countLatePayments($agencyId),
            'new_complaints' => $this->countRows('complaints', $agencyId, ['status' => 'new']),
            'appointments' => $this->countToday('appointments', $agencyId, 'start_at'),
            'owner_payments' => $this->countPendingOwnerPayments($agencyId),
        ];
    }

    public function refreshDashboard(User $user): array
    {
        // TODO: Add cache invalidation once dashboard responses are cached.
        return $this->getDashboard($user);
    }

    private function buildDashboard(User $user, string $role): array
    {
        $agencyId = (int) $user->agency_id;

        return [
            'widgets' => $this->widgetsFor($user, $role),
            'statistics' => $this->generateStatistics($agencyId),
            'charts' => $this->generateCharts($agencyId),
            'shortcuts' => $this->shortcutsFor($role),
            'alerts' => $this->generateAlerts($user),
            'agenda' => $this->agendaFor($user),
            'notifications' => $this->notificationsFor($user),
            'reporting' => $this->reportService->generateDashboardData($agencyId),
            'services' => [
                'property' => $this->propertyService::class,
                'contract' => $this->contractService::class,
                'complaint' => $this->complaintService::class,
            ],
        ];
    }

    private function widgetsFor(User $user, string $role): array
    {
        return DashboardWidget::query()
            ->where(function ($query) use ($user): void {
                $query->where('user_id', $user->getKey())
                    ->orWhereNull('user_id');
            })
            ->where(function ($query) use ($user): void {
                $query->where('agency_id', $user->agency_id)
                    ->orWhereNull('agency_id');
            })
            ->where('is_visible', true)
            ->orderBy('display_order')
            ->get()
            ->filter(function (DashboardWidget $widget) use ($role): bool {
                $roles = $widget->visible_roles;

                return $roles === null || $roles === [] || in_array($role, $roles, true);
            })
            ->values()
            ->toArray();
    }

    private function shortcutsFor(string $role): array
    {
        return match ($role) {
            'manager' => ['reports', 'financial_closing', 'team_performance', 'settings'],
            'agent' => ['properties', 'appointments', 'contracts', 'clients'],
            'assistant' => ['agenda', 'complaints', 'documents', 'notifications'],
            'owner' => ['properties', 'disbursements', 'documents'],
            'client' => ['requests', 'appointments', 'contracts'],
            default => ['dashboard', 'reports'],
        };
    }

    private function agendaFor(User $user): array
    {
        $today = Carbon::today();

        return [
            'appointments' => DB::table('appointments')
                ->where('agency_id', $user->agency_id)
                ->whereDate('start_at', $today)
                ->where(function ($query) use ($user): void {
                    $query->where('created_by', $user->getKey())
                        ->orWhereExists(function ($subQuery) use ($user): void {
                            $subQuery->selectRaw('1')
                                ->from('appointment_participants')
                                ->whereColumn('appointment_participants.appointment_id', 'appointments.id')
                                ->where('appointment_participants.user_id', $user->getKey())
                                ->whereNull('appointment_participants.deleted_at');
                        });
                })
                ->orderBy('start_at')
                ->limit(10)
                ->get()
                ->toArray(),
            'visits' => DB::table('collaboration_visits')
                ->join('collaborations', 'collaborations.id', '=', 'collaboration_visits.collaboration_id')
                ->where('collaborations.agency_id', $user->agency_id)
                ->whereDate('visit_date', $today)
                ->orderBy('collaboration_visits.visit_date')
                ->limit(10)
                ->get('collaboration_visits.*')
                ->toArray(),
        ];
    }

    private function notificationsFor(User $user): array
    {
        return DB::table('app_notifications')
            ->where('user_id', $user->getKey())
            ->whereIn('status', ['unread', 'pending'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function countRows(string $table, int $agencyId, array $conditions = []): int
    {
        $query = DB::table($table)->where('agency_id', $agencyId);

        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        return (int) $query->count();
    }

    private function countExpiringContracts(int $agencyId): int
    {
        return (int) DB::table('contracts')
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'closed', 'expired'])
            ->whereBetween('end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(30)->toDateString()])
            ->count();
    }

    private function countOpenComplaints(int $agencyId): int
    {
        return (int) DB::table('complaints')
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['closed', 'resolved', 'cancelled'])
            ->count();
    }

    private function countToday(string $table, int $agencyId, string $dateColumn): int
    {
        return (int) DB::table($table)
            ->where('agency_id', $agencyId)
            ->whereDate($dateColumn, Carbon::today())
            ->count();
    }

    private function countTodayVisits(int $agencyId): int
    {
        return (int) DB::table('collaboration_visits')
            ->join('collaborations', 'collaborations.id', '=', 'collaboration_visits.collaboration_id')
            ->where('collaborations.agency_id', $agencyId)
            ->whereDate('collaboration_visits.visit_date', Carbon::today())
            ->count();
    }

    private function countLatePayments(int $agencyId): int
    {
        return (int) DB::table('invoices')
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereDate('due_at', '<', Carbon::today())
            ->count();
    }

    private function countPendingOwnerPayments(int $agencyId): int
    {
        return (int) DB::table('owner_disbursements')
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['paid', 'completed', 'cancelled'])
            ->count();
    }

    private function monthlySeries(
        int $agencyId,
        string $table,
        string $dateColumn,
        string $amountColumn,
        array $conditions = []
    ): array {
        $query = DB::table($table)
            ->where('agency_id', $agencyId)
            ->where($dateColumn, '>=', Carbon::now()->subMonths(11)->startOfMonth());

        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        return $query
            ->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as period")
            ->selectRaw("SUM({$amountColumn}) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    private function statusDistribution(int $agencyId, string $table): array
    {
        return DB::table($table)
            ->where('agency_id', $agencyId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}
