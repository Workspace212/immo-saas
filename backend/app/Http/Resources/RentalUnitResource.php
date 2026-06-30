<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContractDocument;
use App\Models\ContractPayment;
use App\Models\ContractPaymentSchedule;
use App\Models\PropertyAvailability;
use App\Models\RentalParty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalUnitResource extends JsonResource
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
            'contract_id' => $this->contract_id,
            'previous_rental_unit_id' => $this->previous_rental_unit_id,
            'assigned_agent_id' => $this->assigned_agent_id,
            'created_by' => $this->created_by,
            'rental_number' => $this->rental_number,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'rent_amount' => $this->rent_amount,
            'currency' => $this->currency,
            'deposit_amount' => $this->deposit_amount,
            'deposit_status' => $this->deposit_status,
            'is_renewal' => $this->is_renewal,
            'notes' => $this->notes,
            'property' => $this->whenLoaded('property'),
            'contract' => $this->whenLoaded('contract'),
            'parties' => $this->parties(),
            'paymentSchedules' => $this->paymentSchedules(),
            'payments' => $this->payments(),
            'documents' => $this->documents(),
            'availability' => $this->availability(),
            'creator' => $this->whenLoaded('creator'),
            'assignedAgent' => $this->whenLoaded('assignedAgent'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function parties(): array
    {
        return RentalParty::query()
            ->with('client')
            ->where('rental_unit_id', $this->resource->getKey())
            ->orderBy('display_order')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function paymentSchedules(): array
    {
        if ($this->contract_id === null) {
            return [];
        }

        return ContractPaymentSchedule::query()
            ->where('contract_id', $this->contract_id)
            ->orderBy('due_date')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function payments(): array
    {
        if ($this->contract_id === null) {
            return [];
        }

        $scheduleIds = ContractPaymentSchedule::query()
            ->where('contract_id', $this->contract_id)
            ->pluck('id');

        return ContractPayment::query()
            ->whereIn('contract_payment_schedule_id', $scheduleIds)
            ->orderByDesc('payment_date')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function documents(): array
    {
        if ($this->contract_id === null) {
            return [];
        }

        return ContractDocument::query()
            ->where('contract_id', $this->contract_id)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function availability(): array
    {
        return PropertyAvailability::query()
            ->where('rental_unit_id', $this->resource->getKey())
            ->orderBy('start_at')
            ->get()
            ->toArray();
    }
}
