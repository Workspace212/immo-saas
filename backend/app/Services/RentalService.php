<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DisbursementStatus;
use App\Enums\PaymentFrequency;
use App\Enums\PaymentStatus;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractPaymentSchedule;
use App\Models\Property;
use App\Models\RentalParty;
use App\Models\RentalUnit;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RentalService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'parties',
        'schedules',
    ];

    public function createRental(array $data, ?User $user = null): RentalUnit
    {
        return DB::transaction(function () use ($data, $user): RentalUnit {
            $parties = $data['parties'] ?? null;
            $schedules = $data['schedules'] ?? null;

            $rentalData = $this->withoutRelationData($data);
            $rentalData['rental_number'] ??= $this->generateRentalNumber();

            if ($user !== null) {
                $rentalData['created_by'] = $user->getKey();
            }

            // TODO: Validate property availability and tenant eligibility before creating a rental.
            $rentalUnit = RentalUnit::query()->create($rentalData);

            if (is_array($parties)) {
                $this->syncParties($rentalUnit, $parties);
            }

            if (is_array($schedules)) {
                foreach ($schedules as $scheduleData) {
                    if (is_array($scheduleData)) {
                        $this->createRentSchedule($rentalUnit, $scheduleData);
                    }
                }
            }

            return $rentalUnit->refresh();
        });
    }

    public function updateRental(RentalUnit $rentalUnit, array $data, ?User $user = null): RentalUnit
    {
        return DB::transaction(function () use ($rentalUnit, $data, $user): RentalUnit {
            $rentalData = $this->withoutRelationData($data);

            // TODO: Add updated_by support if rental_units receives this audit column.
            unset($user);

            if ($rentalData !== []) {
                $rentalUnit->fill($rentalData);
                $rentalUnit->save();
            }

            if (array_key_exists('parties', $data) && is_array($data['parties'])) {
                $this->syncParties($rentalUnit, $data['parties']);
            }

            if (array_key_exists('schedules', $data) && is_array($data['schedules'])) {
                foreach ($data['schedules'] as $scheduleData) {
                    if (is_array($scheduleData)) {
                        $this->createRentSchedule($rentalUnit, $scheduleData);
                    }
                }
            }

            return $rentalUnit->refresh();
        });
    }

    public function activateRental(RentalUnit $rentalUnit): RentalUnit
    {
        return $this->setStatus($rentalUnit, 'active');
    }

    public function endRental(RentalUnit $rentalUnit): RentalUnit
    {
        return $this->setStatus($rentalUnit, 'ended');
    }

    public function cancelRental(RentalUnit $rentalUnit): RentalUnit
    {
        return $this->setStatus($rentalUnit, 'cancelled');
    }

    public function renewRental(RentalUnit $rentalUnit, array $newData): RentalUnit
    {
        return DB::transaction(function () use ($rentalUnit, $newData): RentalUnit {
            $renewalData = $rentalUnit->replicate([
                'rental_number',
                'status',
                'start_date',
                'end_date',
                'created_at',
                'updated_at',
                'deleted_at',
            ])->toArray();

            $renewalData = array_merge($renewalData, $this->withoutRelationData($newData));
            $renewalData['previous_rental_unit_id'] = $rentalUnit->getKey();
            $renewalData['is_renewal'] = true;
            $renewalData['rental_number'] ??= $this->generateRentalNumber();
            $renewalData['status'] ??= 'draft';

            $historyNote = sprintf('Renewed from rental unit #%s.', $rentalUnit->getKey());
            $renewalData['notes'] = trim((string) ($renewalData['notes'] ?? '') . PHP_EOL . $historyNote);
            // TODO: Move renewal history into a dedicated rental history table.

            $renewedRental = RentalUnit::query()->create($renewalData);

            if (array_key_exists('parties', $newData) && is_array($newData['parties'])) {
                $this->syncParties($renewedRental, $newData['parties']);
            } else {
                $this->syncParties($renewedRental, $this->partiesFromRental($rentalUnit));
            }

            if (array_key_exists('schedules', $newData) && is_array($newData['schedules'])) {
                foreach ($newData['schedules'] as $scheduleData) {
                    if (is_array($scheduleData)) {
                        $this->createRentSchedule($renewedRental, $scheduleData);
                    }
                }
            }

            return $renewedRental->refresh();
        });
    }

    public function syncParties(RentalUnit $rentalUnit, array $parties): void
    {
        DB::transaction(function () use ($rentalUnit, $parties): void {
            $keptIds = [];

            foreach ($parties as $index => $partyData) {
                if (! is_array($partyData)) {
                    continue;
                }

                $clientId = isset($partyData['client_id']) ? (int) $partyData['client_id'] : null;

                // TODO: Validate client agency membership before linking rental parties.
                if ($clientId !== null && ! Client::query()->whereKey($clientId)->exists()) {
                    continue;
                }

                $partyType = $this->enumValue($partyData['party_type'] ?? 'tenant');
                $role = $partyData['role'] ?? 'primary';

                $lookup = [
                    'rental_unit_id' => $rentalUnit->getKey(),
                ];

                if ($clientId !== null) {
                    $lookup['client_id'] = $clientId;
                } else {
                    $lookup['party_type'] = $partyType;
                    $lookup['role'] = $role;
                    $lookup['display_order'] = $partyData['display_order'] ?? $index;
                }

                $party = RentalParty::query()->updateOrCreate(
                    $lookup,
                    [
                        'rental_unit_id' => $rentalUnit->getKey(),
                        'client_id' => $clientId,
                        'party_type' => $partyType,
                        'role' => $role,
                        'display_order' => $partyData['display_order'] ?? $index,
                        'signed' => $partyData['signed'] ?? false,
                        'signature_date' => $partyData['signature_date'] ?? null,
                        'notes' => $partyData['notes'] ?? null,
                    ],
                );

                $keptIds[] = $party->getKey();
            }

            $query = RentalParty::query()->where('rental_unit_id', $rentalUnit->getKey());

            if ($keptIds === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('id', $keptIds)->delete();
        });
    }

    public function generateMonthlyRentSchedules(RentalUnit $rentalUnit, int $months): void
    {
        DB::transaction(function () use ($rentalUnit, $months): void {
            $startDate = $rentalUnit->start_date !== null
                ? Carbon::parse($rentalUnit->start_date)->startOfMonth()
                : now()->startOfMonth();

            for ($i = 0; $i < $months; $i++) {
                $dueDate = $startDate->copy()->addMonthsNoOverflow($i);

                $this->createRentSchedule($rentalUnit, [
                    'schedule_number' => sprintf('RENT-%s-%06d', $dueDate->format('Y-m'), $i + 1),
                    'payment_type' => 'rent',
                    'due_date' => $dueDate->toDateString(),
                    'amount_due' => $rentalUnit->rent_amount ?? 0,
                    'remaining_amount' => $rentalUnit->rent_amount ?? 0,
                    'currency' => $rentalUnit->currency ?? 'MAD',
                    'status' => PaymentStatus::PENDING->value,
                    'owner_disbursement_status' => DisbursementStatus::PENDING->value,
                    'notes' => sprintf('Monthly rent generated with %s frequency.', PaymentFrequency::MONTHLY->value),
                ]);
            }
        });
    }

    public function createRentSchedule(RentalUnit $rentalUnit, array $scheduleData): ContractPaymentSchedule
    {
        return DB::transaction(function () use ($rentalUnit, $scheduleData): ContractPaymentSchedule {
            $contract = $this->contractForRental($rentalUnit);
            $amountDue = (float) ($scheduleData['amount_due'] ?? $rentalUnit->rent_amount ?? 0);
            $amountPaid = (float) ($scheduleData['amount_paid'] ?? 0);
            $remainingAmount = (float) ($scheduleData['remaining_amount'] ?? max(0, $amountDue - $amountPaid));
            $scheduleNumber = $scheduleData['schedule_number'] ?? $this->generateRentScheduleNumber();

            return ContractPaymentSchedule::query()->updateOrCreate(
                [
                    'contract_id' => $contract->getKey(),
                    'schedule_number' => $scheduleNumber,
                ],
                [
                    'payment_type' => $this->enumValue($scheduleData['payment_type'] ?? 'rent'),
                    'due_date' => $scheduleData['due_date'] ?? now()->toDateString(),
                    'amount_due' => $amountDue,
                    'currency' => $scheduleData['currency'] ?? $rentalUnit->currency ?? 'MAD',
                    'amount_paid' => $amountPaid,
                    'remaining_amount' => $remainingAmount,
                    'status' => $scheduleData['status'] ?? $this->statusFromAmounts($amountDue, $remainingAmount),
                    'paid_at' => $scheduleData['paid_at'] ?? ($remainingAmount <= 0 && $amountDue > 0 ? now() : null),
                    'days_late' => $scheduleData['days_late'] ?? 0,
                    'penalty_amount' => $scheduleData['penalty_amount'] ?? null,
                    'penalty_is_manual' => $scheduleData['penalty_is_manual'] ?? false,
                    'owner_amount_due' => $scheduleData['owner_amount_due'] ?? null,
                    'owner_amount_paid' => $scheduleData['owner_amount_paid'] ?? 0,
                    'owner_disbursement_status' => $scheduleData['owner_disbursement_status'] ?? DisbursementStatus::PENDING->value,
                    'receipt_number' => $scheduleData['receipt_number'] ?? null,
                    'receipt_generated_at' => $scheduleData['receipt_generated_at'] ?? null,
                    'notes' => $scheduleData['notes'] ?? null,
                ],
            );
        });
    }

    public function calculateDepositStatus(RentalUnit $rentalUnit): string
    {
        if ((float) ($rentalUnit->deposit_amount ?? 0) <= 0) {
            return 'returned';
        }

        // TODO: Calculate this from real deposit payments/refunds once deposit transactions exist.
        return $rentalUnit->deposit_status ?? 'pending';
    }

    public function generateRentalNumber(): string
    {
        $year = now()->year;

        // TODO: Scope rental numbering by agency and protect against concurrent writes.
        $count = RentalUnit::query()
            ->where('rental_number', 'like', sprintf('RENTAL-%d-%%', $year))
            ->count();

        return sprintf('RENTAL-%d-%06d', $year, $count + 1);
    }

    public function isActive(RentalUnit $rentalUnit): bool
    {
        return $rentalUnit->status === 'active';
    }

    public function hasEnded(RentalUnit $rentalUnit): bool
    {
        return $rentalUnit->status === 'ended'
            || (
                $rentalUnit->end_date !== null
                && Carbon::parse($rentalUnit->end_date)->isPast()
                && $rentalUnit->status !== 'cancelled'
            );
    }

    private function setStatus(RentalUnit $rentalUnit, string $status): RentalUnit
    {
        return DB::transaction(function () use ($rentalUnit, $status): RentalUnit {
            // TODO: Validate allowed rental status transitions before saving.
            $rentalUnit->status = $status;
            $rentalUnit->save();

            return $rentalUnit->refresh();
        });
    }

    private function withoutRelationData(array $data): array
    {
        foreach (self::RELATION_KEYS as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    private function contractForRental(RentalUnit $rentalUnit): Contract
    {
        if ($rentalUnit->contract_id === null) {
            // TODO: Create or link a contract before generating rent schedules for standalone rentals.
            throw new RuntimeException('A rental unit must be linked to a contract before creating rent schedules.');
        }

        $contract = Contract::query()->find($rentalUnit->contract_id);

        if (! $contract instanceof Contract) {
            throw new RuntimeException('The linked contract could not be found.');
        }

        return $contract;
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }

    private function statusFromAmounts(float $amountDue, float $remainingAmount): string
    {
        if ($amountDue <= 0 || $remainingAmount >= $amountDue) {
            return PaymentStatus::PENDING->value;
        }

        return $remainingAmount <= 0
            ? PaymentStatus::PAID->value
            : PaymentStatus::PARTIALLY_PAID->value;
    }

    private function generateRentScheduleNumber(): string
    {
        $now = now();

        // TODO: Replace with a dedicated sequence service for rent schedules.
        $count = ContractPaymentSchedule::query()
            ->where('schedule_number', 'like', sprintf('RENT-%s-%%', $now->format('Y-m')))
            ->count();

        return sprintf('RENT-%s-%06d', $now->format('Y-m'), $count + 1);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function partiesFromRental(RentalUnit $rentalUnit): array
    {
        return RentalParty::query()
            ->where('rental_unit_id', $rentalUnit->getKey())
            ->get()
            ->map(fn (RentalParty $party): array => [
                'client_id' => $party->client_id,
                'party_type' => $party->party_type,
                'role' => $party->role,
                'display_order' => $party->display_order,
                'signed' => false,
                'signature_date' => null,
                'notes' => $party->notes,
            ])
            ->all();
    }
}
