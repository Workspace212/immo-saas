<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DisbursementStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Contract;
use App\Models\ContractPayment;
use App\Models\ContractPaymentSchedule;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\OwnerDisbursement;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function registerContractPayment(
        ContractPaymentSchedule $schedule,
        array $paymentData,
        ?User $user = null,
    ): ContractPayment {
        return DB::transaction(function () use ($schedule, $paymentData, $user): ContractPayment {
            $data = $paymentData;
            $data['contract_payment_schedule_id'] = $schedule->getKey();
            $data['payment_number'] ??= $this->generatePaymentNumber();
            $data['payment_date'] ??= now();
            $data['currency'] ??= $schedule->currency ?? 'MAD';
            $data['payment_method'] = $this->enumValue($data['payment_method'] ?? PaymentMethod::OTHER);
            $data['status'] = $this->enumValue($data['status'] ?? PaymentStatus::COMPLETED);
            $data['receipt_number'] ??= $this->generateReceiptNumber();

            if ($user !== null) {
                $data['received_by'] = $user->getKey();
            }

            $payment = ContractPayment::query()->create($data);
            $this->refreshScheduleTotals($schedule, (float) $payment->amount);

            if (isset($paymentData['financial_account_id'])) {
                $contract = $this->contractForSchedule($schedule);

                $this->createFinancialTransaction([
                    'agency_id' => $contract?->agency_id,
                    'destination_account_id' => $paymentData['financial_account_id'],
                    'transaction_type' => TransactionType::CREDIT,
                    'transaction_date' => $payment->payment_date ?? now(),
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'reference' => $payment->payment_number,
                    'description' => 'Contract payment received.',
                    'status' => 'validated',
                    'notes' => $payment->notes,
                ], $user);
            }

            // TODO: Generate receipt PDF and dispatch payment notifications.
            return $payment->refresh();
        });
    }

    public function cancelContractPayment(ContractPayment $payment, ?User $user = null): ContractPayment
    {
        return DB::transaction(function () use ($payment, $user): ContractPayment {
            $schedule = ContractPaymentSchedule::query()->find($payment->contract_payment_schedule_id);

            $payment->status = PaymentStatus::CANCELLED->value;
            $payment->notes = trim((string) ($payment->notes ?? '') . PHP_EOL . 'Payment cancelled.');
            $payment->save();

            if ($schedule instanceof ContractPaymentSchedule) {
                $this->refreshScheduleTotals($schedule, -1 * (float) $payment->amount);
            }

            // TODO: Create a reversing financial transaction when the original account is traceable.
            unset($user);

            return $payment->refresh();
        });
    }

    public function createOwnerDisbursement(array $data, ?User $user = null): OwnerDisbursement
    {
        return DB::transaction(function () use ($data, $user): OwnerDisbursement {
            $amountDue = (float) ($data['amount_due'] ?? 0);
            $amountPaid = (float) ($data['amount_paid'] ?? 0);

            $data['disbursement_number'] ??= $this->generateDisbursementNumber();
            $data['remaining_amount'] = max(0, $amountDue - $amountPaid);
            $data['status'] = $data['status'] ?? $this->disbursementStatusFromAmounts($amountDue, $data['remaining_amount']);
            $data['currency'] ??= 'MAD';

            if ($user !== null) {
                $data['created_by'] = $user->getKey();
            }

            return OwnerDisbursement::query()->create($data);
        });
    }

    public function payOwnerDisbursement(
        OwnerDisbursement $disbursement,
        array $paymentData,
        ?User $user = null,
    ): OwnerDisbursement {
        return DB::transaction(function () use ($disbursement, $paymentData, $user): OwnerDisbursement {
            $paidAmount = (float) ($paymentData['amount'] ?? $paymentData['amount_paid'] ?? 0);
            $newAmountPaid = (float) $disbursement->amount_paid + $paidAmount;
            $remainingAmount = max(0, (float) $disbursement->amount_due - $newAmountPaid);

            $disbursement->amount_paid = $newAmountPaid;
            $disbursement->remaining_amount = $remainingAmount;
            $disbursement->status = $this->disbursementStatusFromAmounts((float) $disbursement->amount_due, $remainingAmount);
            $disbursement->paid_at = $remainingAmount <= 0 ? ($paymentData['paid_at'] ?? now()) : $disbursement->paid_at;
            $disbursement->payment_method = $this->enumValue($paymentData['payment_method'] ?? $disbursement->payment_method ?? PaymentMethod::OTHER);
            $disbursement->financial_account_id = $paymentData['financial_account_id'] ?? $disbursement->financial_account_id;
            $disbursement->receipt_number = $paymentData['receipt_number'] ?? $disbursement->receipt_number ?? $this->generateReceiptNumber();
            $disbursement->save();

            if (isset($paymentData['financial_account_id'])) {
                $this->createFinancialTransaction([
                    'agency_id' => $disbursement->agency_id,
                    'source_account_id' => $paymentData['financial_account_id'],
                    'transaction_type' => TransactionType::DEBIT,
                    'transaction_date' => $paymentData['paid_at'] ?? now(),
                    'amount' => $paidAmount,
                    'currency' => $disbursement->currency ?? 'MAD',
                    'reference' => $disbursement->disbursement_number,
                    'description' => 'Owner disbursement payment.',
                    'status' => 'validated',
                    'notes' => $paymentData['notes'] ?? null,
                ], $user);
            }

            // TODO: Link disbursement payment to owner receipt documents.
            return $disbursement->refresh();
        });
    }

    public function registerInvoicePayment(Invoice $invoice, array $paymentData, ?User $user = null): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $paymentData, $user): InvoicePayment {
            $data = $paymentData;
            $data['agency_id'] ??= $invoice->agency_id;
            $data['invoice_id'] = $invoice->getKey();
            $data['payment_number'] ??= $this->generatePaymentNumber();
            $data['payment_date'] ??= now();
            $data['currency'] ??= $invoice->currency ?? 'MAD';
            $data['payment_method'] = $this->enumValue($data['payment_method'] ?? PaymentMethod::OTHER);
            $data['status'] = $this->enumValue($data['status'] ?? PaymentStatus::COMPLETED);
            $data['receipt_number'] ??= $this->generateReceiptNumber();

            if ($user !== null) {
                $data['received_by'] = $user->getKey();
            }

            $payment = InvoicePayment::query()->create($data);

            $paidAmount = (float) $invoice->paid_amount + (float) $payment->amount;
            $remainingAmount = max(0, (float) $invoice->total_amount - $paidAmount);

            $invoice->paid_amount = $paidAmount;
            $invoice->remaining_amount = $remainingAmount;
            $invoice->status = $this->invoiceStatusFromAmounts((float) $invoice->total_amount, $remainingAmount);
            $invoice->save();

            if ($payment->financial_account_id !== null) {
                $transaction = $this->createFinancialTransaction([
                    'agency_id' => $invoice->agency_id,
                    'destination_account_id' => $payment->financial_account_id,
                    'transaction_type' => TransactionType::CREDIT,
                    'transaction_date' => $payment->payment_date ?? now(),
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'reference' => $payment->payment_number,
                    'description' => 'Invoice payment received.',
                    'status' => 'validated',
                    'notes' => $payment->notes,
                ], $user);

                $invoice->financial_transaction_id = $transaction->getKey();
                $invoice->save();
            }

            return $payment->refresh();
        });
    }

    public function calculateScheduleStatus(ContractPaymentSchedule $schedule): string
    {
        $amountDue = (float) $schedule->amount_due;
        $remainingAmount = max(0, (float) $schedule->remaining_amount);

        if ($amountDue <= 0 || $remainingAmount >= $amountDue) {
            return PaymentStatus::PENDING->value;
        }

        return $remainingAmount <= 0
            ? PaymentStatus::PAID->value
            : PaymentStatus::PARTIALLY_PAID->value;
    }

    public function calculateDaysLate(ContractPaymentSchedule $schedule): int
    {
        if ($schedule->due_date === null || $schedule->status === PaymentStatus::PAID->value) {
            return 0;
        }

        $dueDate = Carbon::parse($schedule->due_date)->startOfDay();

        if ($dueDate->isFuture()) {
            return 0;
        }

        return $dueDate->diffInDays(now()->startOfDay());
    }

    public function applyLatePenalty(
        ContractPaymentSchedule $schedule,
        float $penaltyAmount,
        bool $manual = true,
    ): ContractPaymentSchedule {
        return DB::transaction(function () use ($schedule, $penaltyAmount, $manual): ContractPaymentSchedule {
            $schedule->penalty_amount = $penaltyAmount;
            $schedule->penalty_is_manual = $manual;
            $schedule->remaining_amount = max(0, (float) $schedule->remaining_amount + $penaltyAmount);
            $schedule->days_late = $this->calculateDaysLate($schedule);

            if ($schedule->status !== PaymentStatus::PAID->value && $schedule->days_late > 0) {
                $schedule->status = PaymentStatus::OVERDUE->value;
            }

            // TODO: Add configurable penalty rules by agency and contract type.
            $schedule->save();

            return $schedule->refresh();
        });
    }

    public function generatePaymentNumber(): string
    {
        $year = now()->year;

        // TODO: Replace with a tenant-aware NumberSequence service.
        $count = ContractPayment::query()
            ->where('payment_number', 'like', sprintf('PAY-%d-%%', $year))
            ->count();

        $invoiceCount = InvoicePayment::query()
            ->where('payment_number', 'like', sprintf('PAY-%d-%%', $year))
            ->count();

        return sprintf('PAY-%d-%06d', $year, $count + $invoiceCount + 1);
    }

    public function generateReceiptNumber(): string
    {
        $year = now()->year;

        // TODO: Replace with a tenant-aware NumberSequence service.
        $contractReceiptCount = ContractPayment::query()
            ->where('receipt_number', 'like', sprintf('RCPT-%d-%%', $year))
            ->count();

        $invoiceReceiptCount = InvoicePayment::query()
            ->where('receipt_number', 'like', sprintf('RCPT-%d-%%', $year))
            ->count();

        return sprintf('RCPT-%d-%06d', $year, $contractReceiptCount + $invoiceReceiptCount + 1);
    }

    public function createFinancialTransaction(array $data, ?User $user = null): FinancialTransaction
    {
        return DB::transaction(function () use ($data, $user): FinancialTransaction {
            $data['transaction_number'] ??= $this->generateTransactionNumber();
            $data['transaction_type'] = $this->enumValue($data['transaction_type'] ?? TransactionType::CREDIT);
            $data['transaction_date'] ??= now();
            $data['currency'] ??= 'MAD';
            $data['status'] ??= 'draft';

            if ($user !== null) {
                $data['created_by'] = $user->getKey();
            }

            $transaction = FinancialTransaction::query()->create($data);
            $this->applyTransactionToAccountBalances($transaction);

            // TODO: Add validation workflow, bank reconciliation hooks, and immutable ledger entries.
            return $transaction->refresh();
        });
    }

    private function refreshScheduleTotals(ContractPaymentSchedule $schedule, float $deltaPaid): void
    {
        $schedule->amount_paid = max(0, (float) $schedule->amount_paid + $deltaPaid);
        $schedule->remaining_amount = max(0, (float) $schedule->amount_due - (float) $schedule->amount_paid);
        $schedule->status = $this->calculateScheduleStatus($schedule);
        $schedule->paid_at = $schedule->remaining_amount <= 0 && (float) $schedule->amount_due > 0
            ? now()
            : null;
        $schedule->days_late = $this->calculateDaysLate($schedule);
        $schedule->save();
    }

    private function contractForSchedule(ContractPaymentSchedule $schedule): ?Contract
    {
        if ($schedule->contract_id === null) {
            return null;
        }

        return Contract::query()->find($schedule->contract_id);
    }

    private function applyTransactionToAccountBalances(FinancialTransaction $transaction): void
    {
        $amount = (float) $transaction->amount;

        if ($transaction->transaction_type === TransactionType::CREDIT->value && $transaction->destination_account_id !== null) {
            $this->adjustAccountBalance((int) $transaction->destination_account_id, $amount);
        }

        if ($transaction->transaction_type === TransactionType::DEBIT->value && $transaction->source_account_id !== null) {
            $this->adjustAccountBalance((int) $transaction->source_account_id, -1 * $amount);
        }

        if ($transaction->transaction_type === TransactionType::TRANSFER->value) {
            if ($transaction->source_account_id !== null) {
                $this->adjustAccountBalance((int) $transaction->source_account_id, -1 * $amount);
            }

            if ($transaction->destination_account_id !== null) {
                $this->adjustAccountBalance((int) $transaction->destination_account_id, $amount);
            }
        }
    }

    private function adjustAccountBalance(int $accountId, float $delta): void
    {
        $account = FinancialAccount::query()->find($accountId);

        if (! $account instanceof FinancialAccount) {
            return;
        }

        $account->current_balance = (float) $account->current_balance + $delta;
        $account->save();
    }

    private function disbursementStatusFromAmounts(float $amountDue, float $remainingAmount): string
    {
        if ($amountDue <= 0) {
            return DisbursementStatus::NOT_APPLICABLE->value;
        }

        if ($remainingAmount <= 0) {
            return DisbursementStatus::PAID->value;
        }

        return $remainingAmount < $amountDue
            ? DisbursementStatus::PARTIALLY_PAID->value
            : DisbursementStatus::PENDING->value;
    }

    private function invoiceStatusFromAmounts(float $totalAmount, float $remainingAmount): string
    {
        if ($totalAmount <= 0 || $remainingAmount <= 0) {
            return PaymentStatus::PAID->value;
        }

        return $remainingAmount < $totalAmount
            ? PaymentStatus::PARTIALLY_PAID->value
            : PaymentStatus::PENDING->value;
    }

    private function generateDisbursementNumber(): string
    {
        $year = now()->year;

        // TODO: Replace with a tenant-aware NumberSequence service.
        $count = OwnerDisbursement::query()
            ->where('disbursement_number', 'like', sprintf('DISB-%d-%%', $year))
            ->count();

        return sprintf('DISB-%d-%06d', $year, $count + 1);
    }

    private function generateTransactionNumber(): string
    {
        $year = now()->year;

        // TODO: Replace with a tenant-aware NumberSequence service.
        $count = FinancialTransaction::query()
            ->where('transaction_number', 'like', sprintf('TRX-%d-%%', $year))
            ->count();

        return sprintf('TRX-%d-%06d', $year, $count + 1);
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
