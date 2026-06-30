<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Models\ClientPropertyRequest;
use App\Models\Collaboration;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\Invoice;
use App\Models\RentalParty;
use App\Models\RentalUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'client_type' => $this->type,
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
            'notes' => $this->notes,
            'status' => $this->status,
            'agency' => $this->whenLoaded('agency'),
            'assignedAgent' => null,
            'properties' => $this->properties(),
            'contracts' => $this->contracts(),
            'rentals' => $this->rentals(),
            'complaints' => $this->complaints(),
            'appointments' => $this->appointments(),
            'collaborations' => $this->collaborations(),
            'invoices' => $this->invoices(),
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
        return ClientPropertyRequest::query()
            ->where('client_id', $this->resource->getKey())
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
            ->where('client_id', $this->resource->getKey())
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
        $rentalIds = RentalParty::query()
            ->where('client_id', $this->resource->getKey())
            ->pluck('rental_unit_id');

        return RentalUnit::query()
            ->with(['property', 'contract'])
            ->whereIn('id', $rentalIds)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function complaints(): array
    {
        return Complaint::query()
            ->with('property')
            ->where('client_id', $this->resource->getKey())
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
            ->where('client_id', $this->resource->getKey())
            ->latest('start_at')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function collaborations(): array
    {
        return Collaboration::query()
            ->with('property')
            ->where('client_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function invoices(): array
    {
        return Invoice::query()
            ->where('client_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function documents(): array
    {
        // TODO: Return client documents when a client document model/table is introduced.
        return [];
    }

    /**
     * @return array<int, mixed>
     */
    private function activities(): array
    {
        // TODO: Return client activities when a client activity model/table is introduced.
        return [];
    }
}
