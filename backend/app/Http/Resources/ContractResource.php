<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContractDocument;
use App\Models\ContractParty;
use App\Models\ContractPayment;
use App\Models\ContractPaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
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
            'previous_contract_id' => $this->previous_contract_id,
            'assigned_agent_id' => $this->assigned_agent_id,
            'created_by' => $this->created_by,
            'contract_number' => $this->contract_number,
            'contract_type' => $this->contract_type,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'signed_at' => $this->signed_at?->toISOString(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'deposit_amount' => $this->deposit_amount,
            'charges_amount' => $this->charges_amount,
            'agency_fee_type' => $this->agency_fee_type,
            'agency_fee_value' => $this->agency_fee_value,
            'agency_fee_amount' => $this->agency_fee_amount,
            'agency_fee_is_manual' => $this->agency_fee_is_manual,
            'owner_amount' => $this->owner_amount,
            'owner_amount_is_manual' => $this->owner_amount_is_manual,
            'notes' => $this->notes,
            'property' => $this->whenLoaded('property'),
            'parties' => $this->parties(),
            'paymentSchedules' => $this->paymentSchedules(),
            'payments' => $this->payments(),
            'documents' => $this->documents(),
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
        return ContractParty::query()
            ->with(['owner', 'client'])
            ->where('contract_id', $this->resource->getKey())
            ->orderBy('display_order')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function paymentSchedules(): array
    {
        return ContractPaymentSchedule::query()
            ->where('contract_id', $this->resource->getKey())
            ->orderBy('due_date')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function payments(): array
    {
        $scheduleIds = ContractPaymentSchedule::query()
            ->where('contract_id', $this->resource->getKey())
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
        return ContractDocument::query()
            ->where('contract_id', $this->resource->getKey())
            ->latest()
            ->get()
            ->toArray();
    }
}
