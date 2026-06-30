<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\CashMovement;
use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\FinancialClosing;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\OwnerDisbursement;
use App\Models\RevenueCenter;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    public function calculateAgencyBalance(int $agencyId): array
    {
        $totalIncome = $this->sumCompletedTransactions($agencyId, 'credit');
        $totalExpenses = $this->sumAgencyExpenses($agencyId);
        $bankBalance = $this->sumAccountBalances($agencyId, 'bank');
        $cashBalance = $this->sumAccountBalances($agencyId, 'cash');
        $ownerDisbursements = $this->sumOwnerDisbursements($agencyId);
        $commissions = $this->sumCommissions($agencyId);

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'bank_balance' => $bankBalance,
            'cash_balance' => $cashBalance,
            'owner_disbursements' => $ownerDisbursements,
            'commissions' => $commissions,
            'net_profit' => $totalIncome - $totalExpenses - $ownerDisbursements - $commissions,
        ];
    }

    public function calculateRevenueBetweenDates(int $agencyId, Carbon $from, Carbon $to): float
    {
        return (float) FinancialTransaction::query()
            ->where('agency_id', $agencyId)
            ->where('transaction_type', 'credit')
            ->whereIn('status', ['validated', 'completed', 'paid'])
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    public function calculateExpensesBetweenDates(int $agencyId, Carbon $from, Carbon $to): float
    {
        return (float) Expense::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_ttc');
    }

    public function calculateNetProfit(int $agencyId, Carbon $from, Carbon $to): float
    {
        return $this->calculateRevenueBetweenDates($agencyId, $from, $to)
            - $this->calculateExpensesBetweenDates($agencyId, $from, $to);
    }

    public function calculateAgentCommission(int $userId): float
    {
        return (float) Commission::query()
            ->where('agent_id', $userId)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->sum('remaining_amount');
    }

    public function calculateOwnerPendingPayments(int $ownerId): float
    {
        return (float) OwnerDisbursement::query()
            ->where('owner_id', $ownerId)
            ->whereNotIn('status', ['cancelled', 'paid', 'completed'])
            ->sum('remaining_amount');
    }

    public function calculateOwnerPaidAmount(int $ownerId): float
    {
        return (float) OwnerDisbursement::query()
            ->where('owner_id', $ownerId)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('amount_paid');
    }

    public function calculateInvoiceOutstanding(int $invoiceId): float
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);

        return (float) $invoice->remaining_amount;
    }

    public function closeFinancialPeriod(int $agencyId, Carbon $date, ?User $user = null): FinancialClosing
    {
        return DB::transaction(function () use ($agencyId, $date, $user): FinancialClosing {
            $from = $date->copy()->startOfMonth();
            $to = $date->copy()->endOfMonth();
            $totalRevenue = $this->calculateRevenueBetweenDates($agencyId, $from, $to);
            $totalExpense = $this->calculateExpensesBetweenDates($agencyId, $from, $to);

            // TODO: Enforce period lock validation and prevent closing when draft transactions exist.
            return FinancialClosing::query()->create([
                'agency_id' => $agencyId,
                'closed_by' => $user?->getKey(),
                'closing_number' => $this->generateFinancialClosingNumber(),
                'closing_type' => 'monthly',
                'period_year' => (int) $date->format('Y'),
                'period_month' => (int) $date->format('m'),
                'closed_at' => Carbon::now(),
                'is_locked' => true,
                'locked_at' => Carbon::now(),
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'net_result' => $totalRevenue - $totalExpense,
                'status' => 'closed',
            ]);
        });
    }

    public function reopenFinancialPeriod(FinancialClosing $closing): FinancialClosing
    {
        return DB::transaction(function () use ($closing): FinancialClosing {
            // TODO: Restrict reopening to authorized roles and require an audit reason.
            $closing->fill([
                'is_locked' => false,
                'locked_at' => null,
                'status' => 'reopened',
            ]);
            $closing->save();

            return $closing->refresh();
        });
    }

    public function generateFinancialSummary(int $agencyId, Carbon $from, Carbon $to): array
    {
        $income = $this->calculateRevenueBetweenDates($agencyId, $from, $to);
        $expenses = $this->calculateExpensesBetweenDates($agencyId, $from, $to);

        return [
            'income' => $income,
            'expenses' => $expenses,
            'profit' => $income - $expenses,
            'commissions' => $this->sumCommissionsBetweenDates($agencyId, $from, $to),
            'owner_payments' => $this->sumOwnerDisbursementsBetweenDates($agencyId, $from, $to),
            'cash' => $this->sumAccountBalances($agencyId, 'cash'),
            'bank' => $this->sumAccountBalances($agencyId, 'bank'),
            'receivables' => $this->sumReceivables($agencyId),
            'payables' => $this->sumPayables($agencyId),
        ];
    }

    public function generateFinancialKPIs(int $agencyId): array
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now()->endOfMonth();
        $monthlyIncome = $this->calculateRevenueBetweenDates($agencyId, $from, $to);
        $monthlyExpenses = $this->calculateExpensesBetweenDates($agencyId, $from, $to);
        $invoiceTotal = (float) Invoice::query()->where('agency_id', $agencyId)->sum('total_amount');
        $invoicePaid = (float) InvoicePayment::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'failed', 'refunded'])
            ->sum('amount');

        // TODO: Replace occupancy calculation with rental inventory data when the reporting model is finalized.
        $occupancyRate = $this->averageRevenueCenterStatistic($agencyId, 'occupancy_rate');

        return [
            'occupancy_rate' => $occupancyRate,
            'collection_rate' => $invoiceTotal > 0 ? round(($invoicePaid / $invoiceTotal) * 100, 2) : 0.0,
            'average_rent' => $this->averageRevenueCenterRevenue($agencyId, 'rental'),
            'average_sale' => $this->averageRevenueCenterRevenue($agencyId, 'sale'),
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'monthly_profit' => $monthlyIncome - $monthlyExpenses,
        ];
    }

    public function createFinancialTransaction(array $data): FinancialTransaction
    {
        return DB::transaction(function () use ($data): FinancialTransaction {
            $transactionData = array_intersect_key($data, array_flip([
                'agency_id',
                'source_account_id',
                'destination_account_id',
                'financial_category_id',
                'revenue_center_id',
                'created_by',
                'validated_by',
                'transaction_number',
                'transaction_type',
                'transaction_date',
                'amount',
                'currency',
                'reference',
                'description',
                'attachment_path',
                'status',
                'validated_at',
                'is_reconciled',
                'reconciled_at',
                'notes',
            ]));

            $transactionData['transaction_number'] ??= $this->generateFinancialTransactionNumber();
            $transactionData['transaction_date'] ??= Carbon::now()->toDateString();
            $transactionData['currency'] ??= 'MAD';
            $transactionData['status'] ??= 'draft';
            $transactionData['is_reconciled'] ??= false;

            // TODO: Update account balances only after validation/posting rules are defined.
            return FinancialTransaction::query()->create($transactionData);
        });
    }

    public function createCashMovement(array $data): CashMovement
    {
        return DB::transaction(function () use ($data): CashMovement {
            $movementData = array_intersect_key($data, array_flip([
                'agency_id',
                'cash_account_id',
                'bank_account_id',
                'created_by',
                'movement_number',
                'movement_type',
                'movement_date',
                'amount',
                'currency',
                'reference',
                'comment',
                'status',
            ]));

            $movementData['movement_number'] ??= $this->generateCashMovementNumber();
            $movementData['movement_date'] ??= Carbon::now();
            $movementData['currency'] ??= 'MAD';
            $movementData['status'] ??= 'completed';

            // TODO: Reflect cash movements in account balances once cash-count controls are finalized.
            return CashMovement::query()->create($movementData);
        });
    }

    public function generateFinancialClosingNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('CLOSE-%s-', $year);

        $lastNumber = FinancialClosing::query()
            ->where('closing_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('closing_number')
            ->value('closing_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    private function sumCompletedTransactions(int $agencyId, string $type): float
    {
        return (float) FinancialTransaction::query()
            ->where('agency_id', $agencyId)
            ->where('transaction_type', $type)
            ->whereIn('status', ['validated', 'completed', 'paid'])
            ->sum('amount');
    }

    private function sumAgencyExpenses(int $agencyId): float
    {
        return (float) Expense::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->sum('amount_ttc');
    }

    private function sumAccountBalances(int $agencyId, string $accountType): float
    {
        return (float) FinancialAccount::query()
            ->where('agency_id', $agencyId)
            ->where('account_type', $accountType)
            ->where('is_active', true)
            ->sum('current_balance');
    }

    private function sumOwnerDisbursements(int $agencyId): float
    {
        return (float) OwnerDisbursement::query()
            ->where('agency_id', $agencyId)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('amount_paid');
    }

    private function sumCommissions(int $agencyId): float
    {
        return (float) CommissionPayment::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'failed', 'refunded'])
            ->sum('amount');
    }

    private function sumCommissionsBetweenDates(int $agencyId, Carbon $from, Carbon $to): float
    {
        return (float) CommissionPayment::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'failed', 'refunded'])
            ->whereBetween('payment_date', [$from, $to])
            ->sum('amount');
    }

    private function sumOwnerDisbursementsBetweenDates(int $agencyId, Carbon $from, Carbon $to): float
    {
        return (float) OwnerDisbursement::query()
            ->where('agency_id', $agencyId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount_paid');
    }

    private function sumReceivables(int $agencyId): float
    {
        return (float) Invoice::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'paid'])
            ->sum('remaining_amount');
    }

    private function sumPayables(int $agencyId): float
    {
        $ownerPayables = (float) OwnerDisbursement::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'paid', 'completed'])
            ->sum('remaining_amount');

        $commissionPayables = (float) Commission::query()
            ->where('agency_id', $agencyId)
            ->whereNotIn('status', ['cancelled', 'paid', 'completed'])
            ->sum('remaining_amount');

        $budgetCommitments = (float) Budget::query()
            ->where('agency_id', $agencyId)
            ->whereIn('status', ['approved', 'active'])
            ->sum('remaining_amount');

        $budgetOverruns = (float) BudgetLine::query()
            ->where('agency_id', $agencyId)
            ->where('variance_amount', '>', 0)
            ->sum('variance_amount');

        return $ownerPayables + $commissionPayables + $budgetCommitments + $budgetOverruns;
    }

    private function averageRevenueCenterStatistic(int $agencyId, string $key): float
    {
        $values = RevenueCenter::query()
            ->where('agency_id', $agencyId)
            ->where('is_active', true)
            ->get(['statistics'])
            ->map(static fn (RevenueCenter $center): ?float => is_array($center->statistics) && isset($center->statistics[$key])
                ? (float) $center->statistics[$key]
                : null)
            ->filter(static fn (?float $value): bool => $value !== null)
            ->values();

        if ($values->isEmpty()) {
            return 0.0;
        }

        return round((float) $values->average(), 2);
    }

    private function averageRevenueCenterRevenue(int $agencyId, string $centerType): float
    {
        return round((float) RevenueCenter::query()
            ->where('agency_id', $agencyId)
            ->where('center_type', $centerType)
            ->where('is_active', true)
            ->avg('revenue_total'), 2);
    }

    private function generateFinancialTransactionNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('FTX-%s-', $year);

        $lastNumber = FinancialTransaction::query()
            ->where('transaction_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    private function generateCashMovementNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('CASH-%s-', $year);

        $lastNumber = CashMovement::query()
            ->where('movement_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('movement_number')
            ->value('movement_number');

        $sequence = 1;

        if (is_string($lastNumber)) {
            $sequence = ((int) substr($lastNumber, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }
}
