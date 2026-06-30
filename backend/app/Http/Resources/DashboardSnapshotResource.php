<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSnapshotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];

        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'created_by' => $this->created_by,
            'snapshot_key' => $this->snapshot_key,
            'snapshot_name' => $metadata['snapshot_name'] ?? $this->snapshot_key,
            'period_type' => $this->period_type,
            'period' => $this->period_type,
            'snapshot_date' => $this->snapshot_date,
            'snapshot' => [
                'key' => $this->snapshot_key,
                'name' => $metadata['snapshot_name'] ?? $this->snapshot_key,
                'period' => $this->period_type,
                'date' => $this->snapshot_date,
            ],
            'creator' => $this->whenLoaded('creator'),
            'metrics' => $this->metric_values ?? [],
            'comparison' => $this->comparison_values ?? [],
            'metadata' => $metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
