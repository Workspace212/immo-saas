<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CollaborationCommission;
use App\Models\CollaborationDocument;
use App\Models\CollaborationMessage;
use App\Models\CollaborationOffer;
use App\Models\CollaborationVisit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollaborationResource extends JsonResource
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
            'requesting_agent_id' => $this->requesting_agent_id,
            'owner_agent_id' => $this->owner_agent_id,
            'created_by' => $this->created_by,
            'collaboration_number' => $this->collaboration_number,
            'collaboration_type' => $this->collaboration_type,
            'status' => $this->status,
            'request_message' => $this->request_message,
            'rejection_reason' => $this->rejection_reason,
            'accepted_at' => $this->accepted_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'property' => $this->whenLoaded('property'),
            'client' => $this->whenLoaded('client'),
            'requestingAgent' => $this->whenLoaded('requestingAgent'),
            'ownerAgent' => $this->whenLoaded('ownerAgent'),
            'creator' => $this->whenLoaded('creator'),
            'messages' => $this->messages(),
            'documents' => $this->documents(),
            'visits' => $this->visits(),
            'offers' => $this->offers(),
            'commissions' => $this->commissions(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function messages(): array
    {
        return CollaborationMessage::query()
            ->with('sender')
            ->where('collaboration_id', $this->resource->getKey())
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function documents(): array
    {
        return CollaborationDocument::query()
            ->with('uploader')
            ->where('collaboration_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function visits(): array
    {
        return CollaborationVisit::query()
            ->with(['property', 'client', 'scheduler'])
            ->where('collaboration_id', $this->resource->getKey())
            ->orderBy('visit_date')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function offers(): array
    {
        return CollaborationOffer::query()
            ->with(['property', 'client', 'submitter'])
            ->where('collaboration_id', $this->resource->getKey())
            ->latest('submitted_at')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function commissions(): array
    {
        return CollaborationCommission::query()
            ->with(['agent', 'validator'])
            ->where('collaboration_id', $this->resource->getKey())
            ->get()
            ->toArray();
    }
}
