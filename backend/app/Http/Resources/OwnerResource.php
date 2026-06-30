<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\OwnerDisbursement;
use App\Models\OwnerDocument;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\RentalUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'owner_type' => $this->type,
            'type' => $this->type,
            'full_name' => $this->full_name,
            'company_name' => $this->company_name,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'cin_passport' => $this->cin_passport,
            'cin' => $this->cin_passport,
            'passport' => $this->cin_passport,
            'ice' => $this->ice,
            'rc' => $this->rc,
            'if_number' => $this->if_number,
            'patente' => $this->patente,
            'representative_name' => $this->representative_name,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'preferred_language' => null,
            'bank_name' => $this->bank_name,
            'rib_iban' => $this->rib_iban,
            'notes' => $this->notes,
            'status' => $this->status,
            'agency' => $this->whenLoaded('agency'),
            'properties' => $this->properties(),
            'contracts' => $this->contracts(),
            'rentals' => $this->rentals(),
            'ownerDisbursements' => $this->ownerDisbursements(),
            'complaints' => $this->complaints(),
            'appointments' => $this->appointments(),
            'documents' => $this->documents(),
            'activities' => $this->activities(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function properties(): array
    {
        $propertyIds = PropertyOwner::query()
            ->where('owner_id', $this->resource->getKey())
            ->pluck('property_id');

        return Property::query()
            ->whereIn('id', $propertyIds)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function contracts(): array
    {
        $contractIds = ContractParty::query()
            ->where('owner_id', $this->resource->getKey())
            ->pluck('contract_id');

        return Contract::query()
            ->with('property')
            ->whereIn('id', $contractIds)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function rentals(): array
    {
        $propertyIds = PropertyOwner::query()
            ->where('owner_id', $this->resource->getKey())
            ->pluck('property_id');

        return RentalUnit::query()
            ->with(['property', 'contract'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function ownerDisbursements(): array
    {
        return OwnerDisbursement::query()
            ->where('owner_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function complaints(): array
    {
        $propertyIds = PropertyOwner::query()
            ->where('owner_id', $this->resource->getKey())
            ->pluck('property_id');

        return Complaint::query()
            ->with('property')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function appointments(): array
    {
        return Appointment::query()
            ->with('property')
            ->where('owner_id', $this->resource->getKey())
            ->latest('start_at')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function documents(): array
    {
        return OwnerDocument::query()
            ->with('uploadedBy')
            ->where('owner_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function activities(): array
    {
        // TODO: Return owner activities when an owner activity model/table is introduced.
        return [];
    }
}
