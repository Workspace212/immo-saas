<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AvailabilityType;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyActivity;
use App\Models\PropertyAvailability;
use App\Models\PropertyDocument;
use App\Models\PropertyMedia;
use App\Models\PropertyOwner;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PropertyService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'owners',
        'activities',
        'availabilities',
        'media',
        'documents',
    ];

    public function createProperty(array $data, ?User $user = null): Property
    {
        return DB::transaction(function () use ($data, $user): Property {
            $owners = $data['owners'] ?? null;
            $activities = $data['activities'] ?? null;
            $availabilities = $data['availabilities'] ?? null;

            $propertyData = $this->withoutRelationData($data);
            $propertyData['reference'] ??= $this->generateReference();

            if ($user !== null) {
                $propertyData['created_by'] = $user->getKey();
            }

            $property = Property::query()->create($propertyData);

            if (is_array($owners)) {
                $this->assignOwners($property, $owners);
            }

            if (is_array($activities)) {
                $this->syncActivities($property, $activities);
            }

            if (is_array($availabilities)) {
                foreach ($availabilities as $availabilityData) {
                    if (is_array($availabilityData)) {
                        $this->addAvailability($property, $availabilityData, $user);
                    }
                }
            }

            return $property->refresh();
        });
    }

    public function updateProperty(Property $property, array $data, ?User $user = null): Property
    {
        return DB::transaction(function () use ($property, $data, $user): Property {
            $propertyData = $this->withoutRelationData($data);

            if ($user !== null) {
                $propertyData['updated_by'] = $user->getKey();
            }

            if ($propertyData !== []) {
                $property->fill($propertyData);
                $property->save();
            }

            if (array_key_exists('owners', $data) && is_array($data['owners'])) {
                $this->assignOwners($property, $data['owners']);
            }

            if (array_key_exists('activities', $data) && is_array($data['activities'])) {
                $this->syncActivities($property, $data['activities']);
            }

            return $property->refresh();
        });
    }

    public function archiveProperty(Property $property, ?User $user = null): Property
    {
        return DB::transaction(function () use ($property, $user): Property {
            $property->status = PropertyStatus::ARCHIVED->value;

            if ($user !== null) {
                $property->updated_by = $user->getKey();
            }

            $property->save();

            return $property->refresh();
        });
    }

    public function assignOwners(Property $property, array $owners): void
    {
        DB::transaction(function () use ($property, $owners): void {
            $ownerIds = [];

            foreach ($owners as $ownerData) {
                if (! is_array($ownerData) || ! isset($ownerData['owner_id'])) {
                    continue;
                }

                $ownerId = (int) $ownerData['owner_id'];

                // TODO: Add agency-level ownership validation before linking owners to properties.
                if (! Owner::query()->whereKey($ownerId)->exists()) {
                    continue;
                }

                $ownerIds[] = $ownerId;

                PropertyOwner::query()->updateOrCreate(
                    [
                        'property_id' => $property->getKey(),
                        'owner_id' => $ownerId,
                    ],
                    [
                        'ownership_percentage' => $ownerData['ownership_percentage'] ?? 100,
                        'is_primary_owner' => $ownerData['is_primary_owner'] ?? false,
                        'notes' => $ownerData['notes'] ?? null,
                    ],
                );
            }

            $query = PropertyOwner::query()->where('property_id', $property->getKey());

            if ($ownerIds === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('owner_id', array_values(array_unique($ownerIds)))->delete();
        });
    }

    public function syncActivities(Property $property, array $activities): void
    {
        DB::transaction(function () use ($property, $activities): void {
            $activityTypes = [];

            foreach ($activities as $activityData) {
                if (! is_array($activityData) || ! isset($activityData['activity_type'])) {
                    continue;
                }

                $activityType = $this->enumValue($activityData['activity_type']);
                $activityTypes[] = $activityType;

                PropertyActivity::query()->updateOrCreate(
                    [
                        'property_id' => $property->getKey(),
                        'activity_type' => $activityType,
                    ],
                    [
                        'price' => $activityData['price'] ?? null,
                        'currency' => $activityData['currency'] ?? 'MAD',
                        'is_active' => $activityData['is_active'] ?? true,
                        'notes' => $activityData['notes'] ?? null,
                    ],
                );
            }

            $query = PropertyActivity::query()->where('property_id', $property->getKey());

            if ($activityTypes === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('activity_type', array_values(array_unique($activityTypes)))->delete();
        });
    }

    public function addMedia(Property $property, array $mediaData, ?User $user = null): PropertyMedia
    {
        return DB::transaction(function () use ($property, $mediaData, $user): PropertyMedia {
            $data = array_merge($mediaData, [
                'property_id' => $property->getKey(),
            ]);

            if ($user !== null) {
                $data['created_by'] = $user->getKey();
            }

            return PropertyMedia::query()->create($data);
        });
    }

    public function addDocument(Property $property, array $documentData, ?User $user = null): PropertyDocument
    {
        return DB::transaction(function () use ($property, $documentData, $user): PropertyDocument {
            $data = array_merge($documentData, [
                'property_id' => $property->getKey(),
            ]);

            if ($user !== null) {
                $data['uploaded_by'] = $user->getKey();
            }

            return PropertyDocument::query()->create($data);
        });
    }

    public function addAvailability(Property $property, array $availabilityData, ?User $user = null): PropertyAvailability
    {
        return DB::transaction(function () use ($property, $availabilityData, $user): PropertyAvailability {
            $data = array_merge($availabilityData, [
                'property_id' => $property->getKey(),
            ]);

            if (isset($data['availability_type'])) {
                $data['availability_type'] = $this->enumValue($data['availability_type']);
            } else {
                $data['availability_type'] = AvailabilityType::AVAILABLE->value;
            }

            if ($user !== null) {
                $data['created_by'] = $user->getKey();
            }

            return PropertyAvailability::query()->create($data);
        });
    }

    public function generateReference(?Property $property = null): string
    {
        $year = now()->year;

        // TODO: Scope reference generation per agency and protect against race conditions.
        $count = Property::query()
            ->where('reference', 'like', sprintf('PROP-%d-%%', $year))
            ->count();

        return sprintf('PROP-%d-%06d', $year, $count + 1);
    }

    public function canShowOwnerDetails(Property $property, User $user): bool
    {
        if ($this->userIsManager($user)) {
            return true;
        }

        if (! Schema::hasTable('property_agents')) {
            // TODO: Replace this fallback when the property_agents table/model is introduced.
            return $this->userIsManager($user);
        }

        $query = DB::table('property_agents')
            ->where('property_id', $property->getKey());

        if (Schema::hasColumn('property_agents', 'user_id')) {
            return $query->where('user_id', $user->getKey())->exists();
        }

        if (Schema::hasColumn('property_agents', 'agent_id')) {
            return $query->where('agent_id', $user->getKey())->exists();
        }

        // TODO: Support the final property_agents schema once it is defined.
        return false;
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

        if ($value instanceof PropertyPurpose) {
            return $value->value;
        }

        if ($value instanceof AvailabilityType) {
            return $value->value;
        }

        return (string) $value;
    }

    private function userIsManager(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('Manager');
    }
}
