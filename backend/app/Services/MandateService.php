<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CommissionType;
use App\Enums\MandateStatus;
use App\Enums\MandateType;
use App\Enums\OperationType;
use App\Models\Mandate;
use App\Models\MandateActivity;
use App\Models\MandateDocument;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MandateService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'owners',
        'activities',
        'documents',
    ];

    public function createMandate(array $data, ?User $user = null): Mandate
    {
        return DB::transaction(function () use ($data, $user): Mandate {
            $owners = $data['owners'] ?? null;
            $activities = $data['activities'] ?? null;
            $documents = $data['documents'] ?? null;

            $mandateData = $this->normalizeMandateData($this->withoutRelationData($data));
            $mandateData['mandate_number'] ??= $this->generateMandateNumber();

            if ($user !== null) {
                $mandateData['created_by'] = $user->getKey();
            }

            $mandate = Mandate::query()->create($mandateData);

            if (is_array($owners)) {
                $this->assignOwners($mandate, $owners);
            }

            if (is_array($activities)) {
                $this->syncActivities($mandate, $activities);
            }

            if (is_array($documents)) {
                foreach ($documents as $documentData) {
                    if (is_array($documentData)) {
                        $this->addDocument($mandate, $documentData, $user);
                    }
                }
            }

            return $mandate->refresh();
        });
    }

    public function updateMandate(Mandate $mandate, array $data, ?User $user = null): Mandate
    {
        return DB::transaction(function () use ($mandate, $data, $user): Mandate {
            $mandateData = $this->normalizeMandateData($this->withoutRelationData($data));

            if ($user !== null && Schema::hasColumn('mandates', 'updated_by')) {
                $mandateData['updated_by'] = $user->getKey();
            }

            if ($mandateData !== []) {
                $mandate->fill($mandateData);
                $mandate->save();
            }

            if (array_key_exists('owners', $data) && is_array($data['owners'])) {
                $this->assignOwners($mandate, $data['owners']);
            }

            if (array_key_exists('activities', $data) && is_array($data['activities'])) {
                $this->syncActivities($mandate, $data['activities']);
            }

            return $mandate->refresh();
        });
    }

    public function activateMandate(Mandate $mandate): Mandate
    {
        return $this->setStatus($mandate, MandateStatus::ACTIVE);
    }

    public function expireMandate(Mandate $mandate): Mandate
    {
        return $this->setStatus($mandate, MandateStatus::EXPIRED);
    }

    public function cancelMandate(Mandate $mandate): Mandate
    {
        return $this->setStatus($mandate, MandateStatus::CANCELLED);
    }

    public function archiveMandate(Mandate $mandate): Mandate
    {
        return DB::transaction(function () use ($mandate): Mandate {
            // TODO: Add a dedicated archived status or archived_at column if mandates need archive semantics.
            $mandate->delete();

            return $mandate->refresh();
        });
    }

    public function assignOwners(Mandate $mandate, array $owners): void
    {
        DB::transaction(function () use ($mandate, $owners): void {
            $ownerIds = [];
            $primaryOwnerId = null;

            foreach ($owners as $ownerData) {
                if (! is_array($ownerData) || ! isset($ownerData['owner_id'])) {
                    continue;
                }

                $ownerId = (int) $ownerData['owner_id'];

                // TODO: Validate owner agency ownership before linking it to a mandate.
                if (! Owner::query()->whereKey($ownerId)->exists()) {
                    continue;
                }

                $ownerIds[] = $ownerId;

                if ($primaryOwnerId === null || ($ownerData['is_primary_owner'] ?? false)) {
                    $primaryOwnerId = $ownerId;
                }

                if ($mandate->property_id !== null) {
                    PropertyOwner::query()->updateOrCreate(
                        [
                            'property_id' => $mandate->property_id,
                            'owner_id' => $ownerId,
                        ],
                        [
                            'ownership_percentage' => $ownerData['ownership_percentage'] ?? 100,
                            'is_primary_owner' => $ownerData['is_primary_owner'] ?? false,
                            'notes' => $ownerData['notes'] ?? null,
                        ],
                    );
                }
            }

            if ($primaryOwnerId !== null) {
                $mandate->owner_id = $primaryOwnerId;
                $mandate->save();
            }

            if ($mandate->property_id === null) {
                return;
            }

            $query = PropertyOwner::query()->where('property_id', $mandate->property_id);

            if ($ownerIds === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('owner_id', array_values(array_unique($ownerIds)))->delete();
        });
    }

    public function syncActivities(Mandate $mandate, array $activities): void
    {
        DB::transaction(function () use ($mandate, $activities): void {
            if (! class_exists(MandateActivity::class)) {
                // TODO: Implement mandate activity persistence when MandateActivity exists.
                return;
            }

            $table = (new MandateActivity())->getTable();

            if (! Schema::hasTable($table)) {
                // TODO: Implement mandate activity persistence when the table exists.
                return;
            }

            $activityTypes = [];

            foreach ($activities as $activityData) {
                if (! is_array($activityData) || ! isset($activityData['activity_type'])) {
                    continue;
                }

                $activityType = $this->enumValue($activityData['activity_type']);
                $activityTypes[] = $activityType;

                MandateActivity::query()->updateOrCreate(
                    [
                        'mandate_id' => $mandate->getKey(),
                        'activity_type' => $activityType,
                    ],
                    array_merge($activityData, [
                        'mandate_id' => $mandate->getKey(),
                        'activity_type' => $activityType,
                    ]),
                );
            }

            $query = MandateActivity::query()->where('mandate_id', $mandate->getKey());

            if ($activityTypes === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('activity_type', array_values(array_unique($activityTypes)))->delete();
        });
    }

    public function addDocument(Mandate $mandate, array $documentData, ?User $user = null): MandateDocument
    {
        return DB::transaction(function () use ($mandate, $documentData, $user): MandateDocument {
            if (! class_exists(MandateDocument::class)) {
                throw new RuntimeException('MandateDocument model is not available yet.');
            }

            $table = (new MandateDocument())->getTable();

            if (! Schema::hasTable($table)) {
                throw new RuntimeException('Mandate documents table is not available yet.');
            }

            $data = array_merge($documentData, [
                'mandate_id' => $mandate->getKey(),
            ]);

            if ($user !== null) {
                $data['uploaded_by'] = $user->getKey();
            }

            return MandateDocument::query()->create($data);
        });
    }

    public function generateMandateNumber(): string
    {
        $year = now()->year;

        // TODO: Scope mandate numbering by agency and protect this counter from concurrent writes.
        $count = Mandate::query()
            ->where('mandate_number', 'like', sprintf('MAND-%d-%%', $year))
            ->count();

        return sprintf('MAND-%d-%06d', $year, $count + 1);
    }

    public function canBeConvertedToContract(Mandate $mandate): bool
    {
        // TODO: Validate dates, commissions, mandate party type, and contract prerequisites.
        return $mandate->status === MandateStatus::ACTIVE->value
            && $mandate->property_id !== null
            && Property::query()->whereKey($mandate->property_id)->exists()
            && $this->hasAtLeastOneOwner($mandate);
    }

    public function hasExpired(Mandate $mandate): bool
    {
        return $mandate->end_date !== null
            && Carbon::parse($mandate->end_date)->isPast()
            && $mandate->status !== MandateStatus::CANCELLED->value;
    }

    public function renew(Mandate $mandate, array $newData): Mandate
    {
        return DB::transaction(function () use ($mandate, $newData): Mandate {
            $renewalData = $mandate->replicate([
                'mandate_number',
                'status',
                'start_date',
                'end_date',
                'created_at',
                'updated_at',
                'deleted_at',
            ])->toArray();

            $renewalData = array_merge(
                $renewalData,
                $this->normalizeMandateData($this->withoutRelationData($newData)),
            );

            $renewalData['mandate_number'] ??= $this->generateMandateNumber();
            $renewalData['status'] ??= MandateStatus::DRAFT->value;

            if (Schema::hasColumn('mandates', 'previous_mandate_id')) {
                $renewalData['previous_mandate_id'] = $mandate->getKey();
            } else {
                $historyNote = sprintf('Renewed from mandate #%s.', $mandate->getKey());
                $renewalData['notes'] = trim(($renewalData['notes'] ?? '') . PHP_EOL . $historyNote);
                // TODO: Persist renewal history in a dedicated previous_mandate_id column or history table.
            }

            $renewedMandate = Mandate::query()->create($renewalData);

            if (array_key_exists('owners', $newData) && is_array($newData['owners'])) {
                $this->assignOwners($renewedMandate, $newData['owners']);
            } else {
                $this->assignOwners($renewedMandate, $this->ownersFromMandate($mandate));
            }

            if (array_key_exists('activities', $newData) && is_array($newData['activities'])) {
                $this->syncActivities($renewedMandate, $newData['activities']);
            }

            return $renewedMandate->refresh();
        });
    }

    private function setStatus(Mandate $mandate, MandateStatus $status): Mandate
    {
        return DB::transaction(function () use ($mandate, $status): Mandate {
            $mandate->status = $status->value;
            $mandate->save();

            return $mandate->refresh();
        });
    }

    private function normalizeMandateData(array $data): array
    {
        foreach (['status', 'mandate_type', 'operation_type', 'commission_type'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] instanceof BackedEnum) {
                $data[$key] = $this->enumValue($data[$key]);
            }
        }

        if (($data['mandate_type'] ?? null) instanceof MandateType) {
            $data['mandate_type'] = $data['mandate_type']->value;
        }

        if (($data['operation_type'] ?? null) instanceof OperationType) {
            $data['operation_type'] = $data['operation_type']->value;
        }

        if (($data['commission_type'] ?? null) instanceof CommissionType) {
            $data['commission_type'] = $data['commission_type']->value;
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

    private function hasAtLeastOneOwner(Mandate $mandate): bool
    {
        if ($mandate->owner_id !== null) {
            return Owner::query()->whereKey($mandate->owner_id)->exists();
        }

        if ($mandate->property_id === null) {
            return false;
        }

        return PropertyOwner::query()
            ->where('property_id', $mandate->property_id)
            ->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ownersFromMandate(Mandate $mandate): array
    {
        if ($mandate->property_id !== null) {
            return PropertyOwner::query()
                ->where('property_id', $mandate->property_id)
                ->get()
                ->map(fn (PropertyOwner $owner): array => [
                    'owner_id' => $owner->owner_id,
                    'ownership_percentage' => $owner->ownership_percentage,
                    'is_primary_owner' => $owner->is_primary_owner,
                    'notes' => $owner->notes,
                ])
                ->all();
        }

        if ($mandate->owner_id === null) {
            return [];
        }

        return [
            [
                'owner_id' => $mandate->owner_id,
                'ownership_percentage' => 100,
                'is_primary_owner' => true,
                'notes' => null,
            ],
        ];
    }
}
