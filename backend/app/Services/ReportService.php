<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Report;
use App\Models\ReportExport;
use App\Models\ReportShare;
use App\Models\SavedReport;
use App\Models\ScheduledReport;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(
        private readonly FinancialService $financialService,
    ) {
    }

    public function generateFinancialReport(int $agencyId, Carbon $from, Carbon $to): array
    {
        return [
            'period' => $this->period($from, $to),
            'summary' => $this->financialService->generateFinancialSummary($agencyId, $from, $to),
            'kpis' => $this->financialService->generateFinancialKPIs($agencyId),
        ];
    }

    public function generateSalesReport(int $agencyId, Carbon $from, Carbon $to): array
    {
        $contracts = DB::table('contracts')
            ->where('agency_id', $agencyId)
            ->whereIn('contract_type', ['sale', 'sales'])
            ->whereBetween('signed_at', [$from, $to]);

        // TODO: Replace status/type heuristics with agency-configurable sales pipeline rules.
        return [
            'period' => $this->period($from, $to),
            'total_sales' => (float) (clone $contracts)->sum('amount'),
            'sales_count' => (int) (clone $contracts)->count(),
            'agency_fees' => (float) (clone $contracts)->sum('agency_fee_amount'),
            'owner_amount' => (float) (clone $contracts)->sum('owner_amount'),
            'by_agent' => (clone $contracts)
                ->select('assigned_agent_id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(amount) as total_amount'))
                ->groupBy('assigned_agent_id')
                ->get()
                ->toArray(),
        ];
    }

    public function generateRentalReport(int $agencyId, Carbon $from, Carbon $to): array
    {
        $contracts = DB::table('contracts')
            ->where('agency_id', $agencyId)
            ->whereIn('contract_type', ['rental', 'rent'])
            ->whereBetween('start_date', [$from->toDateString(), $to->toDateString()]);

        // TODO: Add occupancy and renewal metrics once rental inventory rules are finalized.
        return [
            'period' => $this->period($from, $to),
            'rental_count' => (int) (clone $contracts)->count(),
            'rental_income' => (float) (clone $contracts)->sum('amount'),
            'deposits' => (float) (clone $contracts)->sum('deposit_amount'),
            'charges' => (float) (clone $contracts)->sum('charges_amount'),
            'agency_fees' => (float) (clone $contracts)->sum('agency_fee_amount'),
            'by_status' => (clone $contracts)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
        ];
    }

    public function generateComplaintReport(int $agencyId, Carbon $from, Carbon $to): array
    {
        $complaints = DB::table('complaints')
            ->where('agency_id', $agencyId)
            ->whereBetween('created_at', [$from, $to]);

        // TODO: Add SLA breach and resolution-time calculations once SLA rules are configured.
        return [
            'period' => $this->period($from, $to),
            'total' => (int) (clone $complaints)->count(),
            'by_status' => (clone $complaints)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'by_priority' => (clone $complaints)
                ->select('priority', DB::raw('COUNT(*) as total'))
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->toArray(),
            'assigned' => (int) (clone $complaints)->whereNotNull('assigned_to')->count(),
            'closed' => (int) (clone $complaints)->where('status', 'closed')->count(),
        ];
    }

    public function generateAgentPerformanceReport(int $agencyId, Carbon $from, Carbon $to): array
    {
        $contracts = DB::table('contracts')
            ->where('agency_id', $agencyId)
            ->whereBetween('created_at', [$from, $to]);

        $commissions = DB::table('commissions')
            ->where('agency_id', $agencyId)
            ->whereBetween('created_at', [$from, $to]);

        // TODO: Include leads, visits, conversion rates, and quality scoring when those definitions settle.
        return [
            'period' => $this->period($from, $to),
            'contracts_by_agent' => (clone $contracts)
                ->select('assigned_agent_id', DB::raw('COUNT(*) as contracts_count'), DB::raw('SUM(amount) as total_amount'))
                ->groupBy('assigned_agent_id')
                ->get()
                ->toArray(),
            'commissions_by_agent' => (clone $commissions)
                ->select('agent_id', DB::raw('SUM(calculated_amount) as calculated_amount'), DB::raw('SUM(paid_amount) as paid_amount'))
                ->groupBy('agent_id')
                ->get()
                ->toArray(),
        ];
    }

    public function generateDashboardData(int $agencyId): array
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now()->endOfMonth();

        return [
            'financial' => $this->financialService->generateFinancialSummary($agencyId, $from, $to),
            'kpis' => $this->financialService->generateFinancialKPIs($agencyId),
            'reports' => [
                'active' => Report::query()->where('agency_id', $agencyId)->where('is_active', true)->count(),
                'favorites' => Report::query()->where('agency_id', $agencyId)->where('is_favorite', true)->count(),
                'scheduled' => ScheduledReport::query()->where('agency_id', $agencyId)->where('is_active', true)->count(),
            ],
            'recent_exports' => ReportExport::query()
                ->where('agency_id', $agencyId)
                ->latest()
                ->limit(5)
                ->get()
                ->toArray(),
        ];
    }

    public function saveReport(array $data, ?User $user = null): SavedReport
    {
        return DB::transaction(function () use ($data, $user): SavedReport {
            $reportData = array_intersect_key($data, array_flip([
                'agency_id',
                'report_id',
                'user_id',
                'name',
                'slug',
                'configuration',
                'is_shared',
                'is_default',
                'notes',
            ]));

            $reportData['user_id'] ??= $user?->getKey();
            $reportData['slug'] ??= Str::slug((string) ($reportData['name'] ?? 'saved-report'));
            $reportData['is_shared'] ??= false;
            $reportData['is_default'] ??= false;

            // TODO: Enforce per-user default uniqueness and saved report permissions.
            return SavedReport::query()->create($reportData);
        });
    }

    public function scheduleReport(array $data, ?User $user = null): ScheduledReport
    {
        return DB::transaction(function () use ($data, $user): ScheduledReport {
            $scheduleData = array_intersect_key($data, array_flip([
                'agency_id',
                'report_id',
                'saved_report_id',
                'created_by',
                'name',
                'frequency',
                'send_time',
                'recipients',
                'email_enabled',
                'whatsapp_enabled',
                'last_sent_at',
                'next_run_at',
                'is_active',
                'notes',
            ]));

            $scheduleData['created_by'] ??= $user?->getKey();
            $scheduleData['email_enabled'] ??= true;
            $scheduleData['whatsapp_enabled'] ??= false;
            $scheduleData['is_active'] ??= true;

            // TODO: Calculate next_run_at from frequency/send_time and validate recipients by channel.
            return ScheduledReport::query()->create($scheduleData);
        });
    }

    public function shareReport(array $data, ?User $user = null): ReportShare
    {
        return DB::transaction(function () use ($data, $user): ReportShare {
            $shareData = array_intersect_key($data, array_flip([
                'agency_id',
                'report_id',
                'saved_report_id',
                'shared_with_user_id',
                'shared_by',
                'role',
                'can_view',
                'can_edit',
                'expires_at',
                'notes',
            ]));

            $shareData['shared_by'] ??= $user?->getKey();
            $shareData['can_view'] ??= true;
            $shareData['can_edit'] ??= false;

            // TODO: Validate sharing scope, roles, and agency membership before creating shares.
            return ReportShare::query()->create($shareData);
        });
    }

    public function exportPdf(Report $report): ReportExport
    {
        return $this->createExportPlaceholder($report, 'pdf');
    }

    public function exportExcel(Report $report): ReportExport
    {
        return $this->createExportPlaceholder($report, 'xlsx');
    }

    public function exportCsv(Report $report): ReportExport
    {
        return $this->createExportPlaceholder($report, 'csv');
    }

    public function generateReportNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('REPORT-%s-', $year);

        $lastNumber = Report::query()
            ->where('report_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('report_number')
            ->value('report_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    /**
     * @return array{from: string, to: string}
     */
    private function period(Carbon $from, Carbon $to): array
    {
        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    private function createExportPlaceholder(Report $report, string $format): ReportExport
    {
        return DB::transaction(function () use ($report, $format): ReportExport {
            // TODO: Generate the actual export file, persist file_path/file_size, and dispatch notifications.
            return ReportExport::query()->create([
                'agency_id' => $report->agency_id,
                'report_id' => $report->getKey(),
                'requested_by' => $report->created_by,
                'export_number' => $this->generateExportNumber(),
                'export_format' => $format,
                'status' => 'pending',
                'parameters' => [
                    'report_type' => $report->report_type,
                    'filters' => $report->filters,
                    'period_start' => $report->period_start?->toDateString(),
                    'period_end' => $report->period_end?->toDateString(),
                ],
                'started_at' => Carbon::now(),
            ]);
        });
    }

    private function generateExportNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('EXPORT-%s-', $year);

        $lastNumber = ReportExport::query()
            ->where('export_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('export_number')
            ->value('export_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }
}
