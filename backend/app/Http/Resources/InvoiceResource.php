<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\FinancialAccount;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'client_id' => $this->client_id,
            'owner_id' => $this->owner_id,
            'contract_id' => $this->contract_id,
            'financial_transaction_id' => $this->financial_transaction_id,
            'created_by' => $this->created_by,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->toDateString(),
            'due_at' => $this->due_at?->toDateString(),
            'currency' => $this->currency,
            'subtotal_amount' => $this->subtotal_amount,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'remaining_amount' => $this->remaining_amount,
            'pdf_path' => $this->pdf_path,
            'notes' => $this->notes,
            'client' => $this->whenLoaded('client'),
            'owner' => $this->whenLoaded('owner'),
            'property' => $this->property(),
            'contract' => $this->whenLoaded('contract'),
            'lines' => $this->lines(),
            'payments' => $this->payments(),
            'creator' => $this->whenLoaded('creator'),
            'financialAccount' => $this->financialAccount(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function property(): mixed
    {
        if ($this->contract === null || $this->contract->property_id === null) {
            return null;
        }

        return Property::query()->find($this->contract->property_id);
    }

    /**
     * @return array<int, mixed>
     */
    private function lines(): array
    {
        return InvoiceLine::query()
            ->where('invoice_id', $this->resource->getKey())
            ->orderBy('display_order')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function payments(): array
    {
        return InvoicePayment::query()
            ->where('invoice_id', $this->resource->getKey())
            ->latest('payment_date')
            ->get()
            ->toArray();
    }

    private function financialAccount(): mixed
    {
        $accountId = InvoicePayment::query()
            ->where('invoice_id', $this->resource->getKey())
            ->whereNotNull('financial_account_id')
            ->latest('payment_date')
            ->value('financial_account_id');

        return $accountId === null ? null : FinancialAccount::query()->find($accountId);
    }
}
