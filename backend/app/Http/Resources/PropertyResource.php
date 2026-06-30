<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PropertyActivity;
use App\Models\PropertyAvailability;
use App\Models\PropertyDocument;
use App\Models\PropertyMedia;
use App\Models\PropertyOwner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'property_type_id' => $this->property_type_id,
            'reference' => $this->reference,
            'title' => $this->title,
            'description' => $this->description,
            'country' => $this->country,
            'city' => $this->city,
            'sector' => $this->sector,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'living_area_m2' => $this->living_area_m2,
            'land_area_m2' => $this->land_area_m2,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'floors' => $this->floors,
            'year_built' => $this->year_built,
            'status' => $this->status,
            'is_published' => $this->is_published,
            'confidentiality_level' => $this->confidentiality_level,
            'property_type' => $this->whenLoaded('propertyType'),
            'owners' => $this->owners(),
            'activities' => $this->activities(),
            'media' => $this->media(),
            'documents' => $this->documents(),
            'availabilities' => $this->availabilities(),
            'created_by' => $this->whenLoaded('creator'),
            'updated_by' => $this->whenLoaded('updater'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function owners(): array
    {
        return PropertyOwner::query()
            ->with('owner')
            ->where('property_id', $this->resource->getKey())
            ->get()
            ->map(static fn (PropertyOwner $propertyOwner): array => [
                'id' => $propertyOwner->id,
                'owner_id' => $propertyOwner->owner_id,
                'ownership_percentage' => $propertyOwner->ownership_percentage,
                'is_primary_owner' => $propertyOwner->is_primary_owner,
                'notes' => $propertyOwner->notes,
                'owner' => $propertyOwner->owner,
            ])
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function activities(): array
    {
        return PropertyActivity::query()
            ->where('property_id', $this->resource->getKey())
            ->orderBy('activity_type')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function media(): array
    {
        return PropertyMedia::query()
            ->where('property_id', $this->resource->getKey())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function documents(): array
    {
        return PropertyDocument::query()
            ->where('property_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function availabilities(): array
    {
        return PropertyAvailability::query()
            ->where('property_id', $this->resource->getKey())
            ->orderBy('start_at')
            ->get()
            ->toArray();
    }
}
