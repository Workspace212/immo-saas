<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ReportExport;
use App\Models\ReportShare;
use App\Models\ScheduledReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $parameters = is_array($this->parameters) ? $this->parameters : [];

        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'created_by' => $this->created_by,
            'report_number' => $this->report_number,
            'name' => $this->name,
            'report_name' => $this->name,
            'report_type' => $this->report_type,
            'category' => $this->category,
            'parameters' => $parameters,
            'filters' => $this->filters ?? [],
            'columns' => $parameters['columns'] ?? [],
            'grouping' => $parameters['grouping'] ?? [],
            'sorting' => $parameters['sorting'] ?? [],
            'format' => $parameters['format'] ?? null,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'visibility' => $this->visibility,
            'is_favorite' => $this->is_favorite,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'creator' => $this->whenLoaded('creator'),
            'exports' => $this->exports(),
            'scheduledReports' => $this->scheduledReports(),
            'sharedUsers' => $this->sharedUsers(),
            'metadata' => [
                'schedule' => $parameters['schedule'] ?? null,
                'sharing' => $parameters['sharing'] ?? null,
                'columns' => $parameters['columns'] ?? [],
                'grouping' => $parameters['grouping'] ?? [],
                'sorting' => $parameters['sorting'] ?? [],
                'format' => $parameters['format'] ?? null,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function exports(): array
    {
        if ($this->resource->relationLoaded('exports')) {
            return $this->resource->exports->toArray();
        }

        return ReportExport::query()
            ->where('report_id', $this->id)
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function scheduledReports(): array
    {
        if ($this->resource->relationLoaded('scheduledReports')) {
            return $this->resource->scheduledReports->toArray();
        }

        return ScheduledReport::query()
            ->where('report_id', $this->id)
            ->latest()
            ->get()
            ->toArray();
    }

    private function sharedUsers(): array
    {
        if ($this->resource->relationLoaded('sharedUsers')) {
            return $this->resource->sharedUsers->toArray();
        }

        return ReportShare::query()
            ->with('sharedWithUser')
            ->where('report_id', $this->id)
            ->latest()
            ->get()
            ->toArray();
    }
}
