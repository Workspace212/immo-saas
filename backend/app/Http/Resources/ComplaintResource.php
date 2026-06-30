<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ComplaintDocument;
use App\Models\ComplaintProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'property_id' => $this->property_id,
            'client_id' => $this->client_id,
            'created_by' => $this->created_by,
            'assigned_to' => $this->assigned_to,
            'complaint_number' => $this->complaint_number,
            'complaint_type' => $this->complaint_type,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'provider_name' => $this->provider_name,
            'provider_phone' => $this->provider_phone,
            'intervention_date' => $this->intervention_date?->toDateString(),
            'amount_paid' => $this->amount_paid,
            'paid_by' => $this->paid_by,
            'notes' => $this->notes,
            'property' => $this->whenLoaded('property'),
            'client' => $this->whenLoaded('client'),
            'creator' => $this->whenLoaded('creator'),
            'assignee' => $this->whenLoaded('assignee'),
            'documents' => $this->documents(),
            'providers' => $this->providers(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function documents(): array
    {
        return ComplaintDocument::query()
            ->with('uploader')
            ->where('complaint_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function providers(): array
    {
        return ComplaintProvider::query()
            ->with('provider')
            ->where('complaint_id', $this->resource->getKey())
            ->latest('assigned_at')
            ->get()
            ->toArray();
    }
}
