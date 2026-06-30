<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InspectionType;
use App\Models\Property;
use App\Models\PropertyInspection;
use App\Models\PropertyInspectionPhoto;
use App\Models\PropertyInspectionRoom;
use App\Models\PropertyInspectionSignature;
use App\Models\RentalUnit;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InspectionService
{
    /**
     * @var array<int, string>
     */
    private const RELATION_KEYS = [
        'rooms',
        'signatures',
    ];

    public function createInspection(array $data, ?User $user = null): PropertyInspection
    {
        return DB::transaction(function () use ($data, $user): PropertyInspection {
            $rooms = $data['rooms'] ?? null;
            $signatures = $data['signatures'] ?? null;

            $inspectionData = $this->normalizeInspectionData($this->withoutRelationData($data));
            $inspectionData['inspection_number'] ??= $this->generateInspectionNumber();
            $inspectionData['status'] ??= 'draft';
            $inspectionData['inspection_date'] ??= now();

            if ($user !== null) {
                $inspectionData['created_by'] = $user->getKey();
            }

            // TODO: Validate rental/property relationship and inspection uniqueness before creation.
            $inspection = PropertyInspection::query()->create($inspectionData);

            if (is_array($rooms)) {
                $this->syncRooms($inspection, $rooms);
            }

            if (is_array($signatures)) {
                foreach ($signatures as $signatureData) {
                    if (is_array($signatureData)) {
                        $this->addSignature($inspection, $signatureData);
                    }
                }
            }

            return $inspection->refresh();
        });
    }

    public function updateInspection(PropertyInspection $inspection, array $data, ?User $user = null): PropertyInspection
    {
        return DB::transaction(function () use ($inspection, $data, $user): PropertyInspection {
            $inspectionData = $this->normalizeInspectionData($this->withoutRelationData($data));

            // TODO: Add updated_by support if property_inspections receives this audit column.
            unset($user);

            if ($inspectionData !== []) {
                $inspection->fill($inspectionData);
                $inspection->save();
            }

            if (array_key_exists('rooms', $data) && is_array($data['rooms'])) {
                $this->syncRooms($inspection, $data['rooms']);
            }

            if (array_key_exists('signatures', $data) && is_array($data['signatures'])) {
                foreach ($data['signatures'] as $signatureData) {
                    if (is_array($signatureData)) {
                        $this->addSignature($inspection, $signatureData);
                    }
                }
            }

            return $inspection->refresh();
        });
    }

    public function syncRooms(PropertyInspection $inspection, array $rooms): void
    {
        DB::transaction(function () use ($inspection, $rooms): void {
            $roomNames = [];

            foreach ($rooms as $index => $roomData) {
                if (! is_array($roomData) || ! isset($roomData['room_name'])) {
                    continue;
                }

                $roomName = (string) $roomData['room_name'];
                $roomNames[] = $roomName;

                $room = PropertyInspectionRoom::query()->updateOrCreate(
                    [
                        'property_inspection_id' => $inspection->getKey(),
                        'room_name' => $roomName,
                    ],
                    [
                        'room_type' => $roomData['room_type'] ?? null,
                        'floor' => $roomData['floor'] ?? null,
                        'condition' => $roomData['condition'] ?? null,
                        'cleanliness' => $roomData['cleanliness'] ?? null,
                        'comments' => $roomData['comments'] ?? null,
                        'display_order' => $roomData['display_order'] ?? $index,
                    ],
                );

                if (isset($roomData['photos']) && is_array($roomData['photos'])) {
                    foreach ($roomData['photos'] as $photoData) {
                        if (is_array($photoData)) {
                            $this->addPhoto($room, $photoData);
                        }
                    }
                }
            }

            $query = PropertyInspectionRoom::query()->where('property_inspection_id', $inspection->getKey());

            if ($roomNames === []) {
                $query->delete();

                return;
            }

            $query->whereNotIn('room_name', array_values(array_unique($roomNames)))->delete();
        });
    }

    public function addPhoto(
        PropertyInspectionRoom $room,
        array $photoData,
        ?User $user = null,
    ): PropertyInspectionPhoto {
        return DB::transaction(function () use ($room, $photoData, $user): PropertyInspectionPhoto {
            $data = array_merge($photoData, [
                'property_inspection_room_id' => $room->getKey(),
            ]);

            if ($user !== null) {
                $data['uploaded_by'] = $user->getKey();
            }

            // TODO: Extract GPS metadata from uploaded files when available.
            return PropertyInspectionPhoto::query()->create($data);
        });
    }

    public function addSignature(
        PropertyInspection $inspection,
        array $signatureData,
    ): PropertyInspectionSignature {
        return DB::transaction(function () use ($inspection, $signatureData): PropertyInspectionSignature {
            $data = array_merge($signatureData, [
                'property_inspection_id' => $inspection->getKey(),
            ]);

            if (($data['signed'] ?? false) === true && ! isset($data['signed_at'])) {
                $data['signed_at'] = now();
            }

            return PropertyInspectionSignature::query()->create($data);
        });
    }

    public function validateInspection(PropertyInspection $inspection, ?User $user = null): PropertyInspection
    {
        return DB::transaction(function () use ($inspection, $user): PropertyInspection {
            // TODO: Require mandatory signatures/photos before validation based on agency rules.
            $inspection->status = 'validated';
            $inspection->validated_by = $user?->getKey();
            $inspection->validation_date = now();
            $inspection->save();

            return $inspection->refresh();
        });
    }

    public function cancelInspection(PropertyInspection $inspection): PropertyInspection
    {
        return DB::transaction(function () use ($inspection): PropertyInspection {
            // TODO: Restrict cancellation after validation unless manager approval is granted.
            $inspection->status = 'cancelled';
            $inspection->save();

            return $inspection->refresh();
        });
    }

    public function markSignatureRefused(
        PropertyInspectionSignature $signature,
        string $reason,
    ): PropertyInspectionSignature {
        return DB::transaction(function () use ($signature, $reason): PropertyInspectionSignature {
            $signature->signed = false;
            $signature->signed_at = null;
            $signature->refused = true;
            $signature->refusal_reason = $reason;
            $signature->save();

            return $signature->refresh();
        });
    }

    public function generateInspectionNumber(): string
    {
        $year = now()->year;

        // TODO: Scope inspection numbering by agency and protect against concurrent writes.
        $count = PropertyInspection::query()
            ->where('inspection_number', 'like', sprintf('INSP-%d-%%', $year))
            ->count();

        return sprintf('INSP-%d-%06d', $year, $count + 1);
    }

    public function createEntryInspectionForRental(RentalUnit $rentalUnit, ?User $user = null): PropertyInspection
    {
        return $this->createInspectionForRental($rentalUnit, InspectionType::ENTRY, $user);
    }

    public function createExitInspectionForRental(RentalUnit $rentalUnit, ?User $user = null): PropertyInspection
    {
        return $this->createInspectionForRental($rentalUnit, InspectionType::EXIT, $user);
    }

    private function createInspectionForRental(
        RentalUnit $rentalUnit,
        InspectionType $inspectionType,
        ?User $user = null,
    ): PropertyInspection {
        if ($rentalUnit->property_id === null) {
            throw new RuntimeException('A rental unit must be linked to a property before creating an inspection.');
        }

        $property = Property::query()->find($rentalUnit->property_id);

        if (! $property instanceof Property) {
            throw new RuntimeException('The rental unit property could not be found.');
        }

        return $this->createInspection([
            'agency_id' => $rentalUnit->agency_id,
            'rental_unit_id' => $rentalUnit->getKey(),
            'property_id' => $property->getKey(),
            'inspection_type' => $inspectionType,
            'inspection_date' => now(),
            'status' => 'draft',
            'notes' => sprintf('%s inspection generated for rental %s.', $inspectionType->value, $rentalUnit->rental_number),
        ], $user);
    }

    private function normalizeInspectionData(array $data): array
    {
        if (array_key_exists('inspection_type', $data) && $data['inspection_type'] instanceof BackedEnum) {
            $data['inspection_type'] = (string) $data['inspection_type']->value;
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
}
