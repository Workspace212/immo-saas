<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CommissionType;
use App\Enums\ContractStatus;
use App\Enums\ContractTypeEnum;
use App\Enums\PaymentFrequency;
use App\Enums\PaymentStatus;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\ContractParty;
use App\Models\ContractPayment;
use App\Models\ContractPaymentSchedule;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContractService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'parties',
        'payment_schedules',
        'documents',
    ];

    public function createContract(array $data, ?User $user = null): Contract
    {
        return DB::transaction(function () use ($data, $user): Contract {
            $parties = $data['parties'] ?? null;
            $paymentSchedules = $data['payment_schedules'] ?? null;
            $documents = $data['documents'] ?? null;

            $contractData = $this->normalizeContractData($this->withoutRelationData($data));
            $contractData['contract_number'] ??= $this->generateContractNumber();

            if ($user !== null) {
                $contractData['created_by'] = $user->getKey();
            }

            $contract = Contract::query()->create($contractData);

            if (is_array($parties)) {
                $this->syncParties($contract, $parties);
            }

            if (is_array($paymentSchedules)) {
                $this->createPaymentSchedules($contract, $paymentSchedules);
            }

            if (is_array($documents)) {
                foreach ($documents as $documentData) {
                    if (is_array($documentData)) {
                        $this->addDocument($contract, $documentData, $user);
                    }
                }
            }

            return $contract->refresh();
        });
    }

    public function updateContract(Contract $contract, array $data, ?User $user = null): Contract
    {
        return DB::transaction(function () use ($contract, $data, $user): Contract {
            $contractData = $this->normalizeContractData($this->withoutRelationData($data));

            // TODO: Add updated_by support if the contracts table gets this audit column.
            unset($user);

            if ($contractData !== []) {
                $contract->fill($contractData);
                $contract->save();
            }

            if (array_key_exists('parties', $data) && is_array($data['parties'])) {
                $this->syncParties($contract, $data['parties']);
            }

            if (array_key_exists('payment_schedules', $data) && is_array($data['payment_schedules'])) {
                $this->createPaymentSchedules($contract, $data['payment_schedules']);
            }

            return $contract->refresh();
        });
    }

    public function activateContract(Contract $contract): Contract
    {
        return $this->setStatus($contract, ContractStatus::ACTIVE);
    }

    public function completeContract(Contract $contract): Contract
    {
        return $this->setStatus($contract, ContractStatus::COMPLETED);
    }

    public function cancelContract(Contract $contract): Contract
    {
        return $this->setStatus($contract, ContractStatus::CANCELLED);
    }

    public function expireContract(Contract $contract): Contract
    {
        return $this->setStatus($contract, ContractStatus::EXPIRED);
    }

    public function renewContract(Contract $contract, array $newData): Contract
    {
        return DB::transaction(function () use ($contract, $newData): Contract {
            $renewalData = $contract->replicate([
                'contract_number',
                'status',
                'start_date',
                'end_date',
                'signed_at',
                'created_at',
                'updated_at',
                'deleted_at',
            ])->toArray();

            $renewalData = array_merge(
                $renewalData,
                $this->normalizeContractData($this->withoutRelationData($newData)),
            );

            $renewalData['previous_contract_id'] = $contract->getKey();
            $renewalData['contract_number'] ??= $this->generateContractNumber();
            $renewalData['status'] ??= ContractStatus::DRAFT->value;

            $historyNote = sprintf('Renewed from contract #%s.', $contract->getKey());
            $renewalData['notes'] = trim((string) ($renewalData['notes'] ?? '') . PHP_EOL . $historyNote);
            // TODO: Move renewal history into a dedicated contract history table.

            $renewedContract = Contract::query()->create($renewalData);

            if (array_key_exists('parties', $newData) && is_array($newData['parties'])) {
                $this->syncParties($renewedContract, $newData['parties']);
            }

            if (array_key_exists('payment_schedules', $newData) && is_array($newData['payment_schedules'])) {
                $this->createPaymentSchedules($renewedContract, $newData['payment_schedules']);
            }

            return $renewedContract->refresh();
        });
    }

    public function syncParties(Contract $contract, array $parties): void
    {
        DB::transaction(function () use ($contract, $parties): void {
            $keptIds = [];

            foreach ($parties as $index => $partyData) {
                if (! is_array($partyData)) {
                    continue;
                }

                $ownerId = isset($partyData['owner_id']) ? (int) $partyData['owner_id'] : null;
                $clientId = isset($partyData['client_id']) ? (int) $partyData['client_id'] : null;

                // TODO: Enforce agency-level party validation before attaching owners or clients.
                if ($ownerId !== null && ! Owner::query()->whereKey($ownerId)->exists()) {
                    continue;
                }

                if ($clientId !== null && ! Client::query()->whereKey($clientId)->exists()) {
                    continue;
                }

                $partyType = $this->enumValue($partyData['party_type'] ?? ($ownerId !== null ? 'owner' : 'client'));
                $lookup = [
                    'contract_id' => $contract->getKey(),
                    'party_type' => $partyType,
                ];

                if ($ownerId !== null) {
                    $lookup['owner_id'] = $ownerId;
                } elseif ($clientId !== null) {
                    $lookup['client_id'] = $clientId;
                } else {
                    $lookup['role'] = $partyData['role'] ?? 'other';
                    $lookup['display_order'] = $partyData['display_order'] ?? $index;
                }

                $party = ContractParty::query()->updateOrCreate(
                    $lookup,
                    [
                        'contract_id' => $contract->getKey(),
                        'owner_id' => $ownerId,
                        'client_id' => $clientId,
                        'party_type' => $partyType,
                        'role' => $partyData['role'] ?? 'other',
                        'ownership_percentage' => $partyData['ownership_percentage'] ?? null,
                        'display_order' => $partyData['display_order'] ?? $index,
                        'signed' => $partyData['signed'] ?? false,
                        'signed_at' => $partyData['signed_at'] ?? null,
                        'is_active' => $partyData['is_active'] ?? true,
                        'notes' => $partyData['notes'] ?? null,
                    ],
                );

                $keptIds[] = $party->getKey();
            }

            $query = ContractParty::query()->where('contract_id', $contract->getKey());

            if ($keptIds === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('id', $keptIds)->delete();
        });
    }

    public function createPaymentSchedules(Contract $contract, array $schedules): void
    {
        DB::transaction(function () use ($contract, $schedules): void {
            $scheduleNumbers = [];

            foreach ($schedules as $index => $scheduleData) {
                if (! is_array($scheduleData)) {
                    continue;
                }

                $scheduleNumber = $scheduleData['schedule_number']
                    ?? sprintf('%s-SCH-%03d', $contract->contract_number, $index + 1);
                $amountDue = (float) ($scheduleData['amount_due'] ?? 0);
                $amountPaid = (float) ($scheduleData['amount_paid'] ?? 0);
                $remainingAmount = max(0, $amountDue - $amountPaid);

                $scheduleNumbers[] = $scheduleNumber;

                ContractPaymentSchedule::query()->updateOrCreate(
                    [
                        'contract_id' => $contract->getKey(),
                        'schedule_number' => $scheduleNumber,
                    ],
                    [
                        'payment_type' => $this->enumValue($scheduleData['payment_type'] ?? 'other'),
                        'due_date' => $scheduleData['due_date'] ?? now()->toDateString(),
                        'amount_due' => $amountDue,
                        'currency' => $scheduleData['currency'] ?? $contract->currency ?? 'MAD',
                        'amount_paid' => $amountPaid,
                        'remaining_amount' => $remainingAmount,
                        'status' => $scheduleData['status'] ?? $this->statusFromRemainingAmount($amountDue, $remainingAmount),
                        'paid_at' => $remainingAmount <= 0 && $amountDue > 0 ? ($scheduleData['paid_at'] ?? now()) : ($scheduleData['paid_at'] ?? null),
                        'days_late' => $scheduleData['days_late'] ?? 0,
                        'penalty_amount' => $scheduleData['penalty_amount'] ?? null,
                        'penalty_is_manual' => $scheduleData['penalty_is_manual'] ?? false,
                        'owner_amount_due' => $scheduleData['owner_amount_due'] ?? null,
                        'owner_amount_paid' => $scheduleData['owner_amount_paid'] ?? 0,
                        'owner_disbursement_status' => $scheduleData['owner_disbursement_status'] ?? 'pending',
                        'receipt_number' => $scheduleData['receipt_number'] ?? null,
                        'receipt_generated_at' => $scheduleData['receipt_generated_at'] ?? null,
                        'notes' => $scheduleData['notes'] ?? null,
                    ],
                );
            }

            $query = ContractPaymentSchedule::query()->where('contract_id', $contract->getKey());

            if ($scheduleNumbers === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('schedule_number', array_values(array_unique($scheduleNumbers)))->delete();
        });
    }

    public function registerPayment(ContractPaymentSchedule $schedule, array $paymentData, ?User $user = null): ContractPayment
    {
        return DB::transaction(function () use ($schedule, $paymentData, $user): ContractPayment {
            $data = $paymentData;
            $data['contract_payment_schedule_id'] = $schedule->getKey();
            $data['payment_number'] ??= $this->generatePaymentNumber();
            $data['payment_date'] ??= now();
            $data['currency'] ??= $schedule->currency ?? 'MAD';
            $data['status'] ??= PaymentStatus::COMPLETED->value;

            if ($user !== null) {
                $data['received_by'] = $user->getKey();
            }

            $payment = ContractPayment::query()->create($data);

            $newAmountPaid = (float) $schedule->amount_paid + (float) $payment->amount;
            $remainingAmount = max(0, (float) $schedule->amount_due - $newAmountPaid);

            $schedule->amount_paid = $newAmountPaid;
            $schedule->remaining_amount = $remainingAmount;
            $schedule->status = $remainingAmount <= 0
                ? PaymentStatus::PAID->value
                : PaymentStatus::PARTIALLY_PAID->value;

            if ($remainingAmount <= 0) {
                $schedule->paid_at = $payment->payment_date ?? now();
            }

            $schedule->save();

            // TODO: Trigger receipt generation and accounting entries after payment registration.
            return $payment->refresh();
        });
    }

    public function addDocument(Contract $contract, array $documentData, ?User $user = null): ContractDocument
    {
        return DB::transaction(function () use ($contract, $documentData, $user): ContractDocument {
            $data = array_merge($documentData, [
                'contract_id' => $contract->getKey(),
            ]);

            if ($user !== null) {
                $data['uploaded_by'] = $user->getKey();
            }

            return ContractDocument::query()->create($data);
        });
    }

    public function generateContractNumber(): string
    {
        $year = now()->year;

        // TODO: Scope numbering by agency and protect from concurrent writes.
        $count = Contract::query()
            ->where('contract_number', 'like', sprintf('CTR-%d-%%', $year))
            ->count();

        return sprintf('CTR-%d-%06d', $year, $count + 1);
    }

    public function calculateAgencyFee(Contract $contract): float
    {
        if ((bool) $contract->agency_fee_is_manual) {
            return (float) ($contract->agency_fee_amount ?? 0);
        }

        $amount = (float) ($contract->amount ?? 0);
        $feeValue = (float) ($contract->agency_fee_value ?? 0);

        return match ($contract->agency_fee_type) {
            CommissionType::PERCENT->value => round($amount * $feeValue / 100, 2),
            CommissionType::FIXED->value => $feeValue,
            CommissionType::ONE_MONTH_RENT->value => $amount,
            default => 0.0,
        };
    }

    public function calculateOwnerAmount(Contract $contract): float
    {
        if ((bool) $contract->owner_amount_is_manual) {
            return (float) ($contract->owner_amount ?? 0);
        }

        $amount = (float) ($contract->amount ?? 0);
        $charges = (float) ($contract->charges_amount ?? 0);

        return max(0, round($amount - $this->calculateAgencyFee($contract) - $charges, 2));
    }

    public function isActive(Contract $contract): bool
    {
        return $contract->status === ContractStatus::ACTIVE->value;
    }

    public function hasExpired(Contract $contract): bool
    {
        return $contract->end_date !== null
            && Carbon::parse($contract->end_date)->isPast()
            && ! in_array($contract->status, [
                ContractStatus::CANCELLED->value,
                ContractStatus::COMPLETED->value,
            ], true);
    }

    private function setStatus(Contract $contract, ContractStatus $status): Contract
    {
        return DB::transaction(function () use ($contract, $status): Contract {
            // TODO: Validate allowed contract status transitions before saving.
            $contract->status = $status->value;
            $contract->save();

            return $contract->refresh();
        });
    }

    private function normalizeContractData(array $data): array
    {
        foreach (['contract_type', 'status', 'agency_fee_type', 'payment_frequency'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] instanceof BackedEnum) {
                $data[$key] = $this->enumValue($data[$key]);
            }
        }

        if (($data['contract_type'] ?? null) instanceof ContractTypeEnum) {
            $data['contract_type'] = $data['contract_type']->value;
        }

        if (($data['status'] ?? null) instanceof ContractStatus) {
            $data['status'] = $data['status']->value;
        }

        if (($data['agency_fee_type'] ?? null) instanceof CommissionType) {
            $data['agency_fee_type'] = $data['agency_fee_type']->value;
        }

        if (($data['payment_frequency'] ?? null) instanceof PaymentFrequency) {
            $data['payment_frequency'] = $data['payment_frequency']->value;
        }

        return $data;
    }

    private function withoutRelationData(array $data): array
    {
        foreach (self::RELATION_KEYS as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }

    private function statusFromRemainingAmount(float $amountDue, float $remainingAmount): string
    {
        if ($amountDue <= 0 || $remainingAmount >= $amountDue) {
            return PaymentStatus::PENDING->value;
        }

        return $remainingAmount <= 0
            ? PaymentStatus::PAID->value
            : PaymentStatus::PARTIALLY_PAID->value;
    }

    private function generatePaymentNumber(): string
    {
        $year = now()->year;

        // TODO: Replace with a dedicated payment number sequence service.
        $count = ContractPayment::query()
            ->where('payment_number', 'like', sprintf('PAY-%d-%%', $year))
            ->count();

        return sprintf('PAY-%d-%06d', $year, $count + 1);
    }
}
