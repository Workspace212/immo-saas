<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Complaint;
use App\Models\ComplaintProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'provider_type' => $this->provider_type,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'ice' => $this->ice,
            'rc' => $this->rc,
            'if_number' => $this->if_number,
            'patente' => $this->patente,
            'rating' => $this->rating,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'agency' => $this->whenLoaded('agency'),
            'complaints' => $this->complaints(),
            'assignedComplaints' => $this->assignedComplaints(),
            'financialTransactions' => $this->financialTransactions(),
            'documents' => $this->documents(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function complaints(): array
    {
        return Complaint::query()
            ->where('agency_id', $this->agency_id)
            ->where(function ($query): void {
                $query->where('provider_phone', $this->phone)
                    ->orWhere('provider_name', $this->company_name)
                    ->orWhere('provider_name', $this->contact_name);
            })
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function assignedComplaints(): array
    {
        return ComplaintProvider::query()
            ->with(['complaint', 'provider'])
            ->where('provider_id', $this->resource->getKey())
            ->latest('assigned_at')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function financialTransactions(): array
    {
        // TODO: Return provider financial transactions when a provider_id relation exists on financial transactions.
        return [];
    }

    /**
     * @return array<int, mixed>
     */
    private function documents(): array
    {
        // TODO: Return provider documents when a provider document model/table is introduced.
        return [];
    }
}
