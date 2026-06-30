<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AppointmentParticipant;
use App\Models\AppointmentReminder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'owner_id' => $this->owner_id,
            'provider_id' => $this->provider_id,
            'contract_id' => $this->contract_id,
            'mandate_id' => $this->mandate_id,
            'complaint_id' => $this->complaint_id,
            'collaboration_id' => $this->collaboration_id,
            'created_by' => $this->created_by,
            'appointment_number' => $this->appointment_number,
            'appointment_type' => $this->appointment_type,
            'title' => $this->title,
            'description' => $this->description,
            'start_at' => $this->start_at?->toISOString(),
            'end_at' => $this->end_at?->toISOString(),
            'location' => $this->location,
            'status' => $this->status,
            'priority' => $this->priority,
            'external_calendar_provider' => $this->external_calendar_provider,
            'external_calendar_id' => $this->external_calendar_id,
            'external_event_id' => $this->external_event_id,
            'sync_status' => $this->sync_status,
            'last_synced_at' => $this->last_synced_at?->toISOString(),
            'property' => $this->whenLoaded('property'),
            'client' => $this->whenLoaded('client'),
            'owner' => $this->whenLoaded('owner'),
            'provider' => $this->whenLoaded('provider'),
            'contract' => $this->whenLoaded('contract'),
            'mandate' => $this->whenLoaded('mandate'),
            'complaint' => $this->whenLoaded('complaint'),
            'collaboration' => $this->whenLoaded('collaboration'),
            'creator' => $this->whenLoaded('creator'),
            'participants' => $this->participants(),
            'reminders' => $this->reminders(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function participants(): array
    {
        return AppointmentParticipant::query()
            ->with(['user', 'client', 'owner', 'provider'])
            ->where('appointment_id', $this->resource->getKey())
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function reminders(): array
    {
        return AppointmentReminder::query()
            ->with('user')
            ->where('appointment_id', $this->resource->getKey())
            ->orderBy('remind_at')
            ->get()
            ->toArray();
    }
}
