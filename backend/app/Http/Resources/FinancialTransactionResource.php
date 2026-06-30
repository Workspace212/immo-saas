<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'source_account_id' => $this->source_account_id,
            'destination_account_id' => $this->destination_account_id,
            'financial_category_id' => $this->financial_category_id,
            'revenue_center_id' => $this->revenue_center_id,
            'created_by' => $this->created_by,
            'validated_by' => $this->validated_by,
            'transaction_number' => $this->transaction_number,
            'transaction_type' => $this->transaction_type,
            'transaction_date' => $this->transaction_date,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'description' => $this->description,
            'attachment_path' => $this->attachment_path,
            'status' => $this->status,
            'validated_at' => $this->validated_at,
            'is_reconciled' => $this->is_reconciled,
            'reconciled_at' => $this->reconciled_at,
            'notes' => $this->notes,
            'transaction' => [
                'number' => $this->transaction_number,
                'type' => $this->transaction_type,
                'date' => $this->transaction_date,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'status' => $this->status,
            ],
            'account' => [
                'source' => $this->whenLoaded('sourceAccount'),
                'destination' => $this->whenLoaded('destinationAccount'),
            ],
            'category' => $this->whenLoaded('financialCategory'),
            'creator' => $this->whenLoaded('creator'),
            'validator' => $this->whenLoaded('validator'),
            'owner' => null,
            'client' => null,
            'provider' => null,
            'property' => $this->whenLoaded('revenueCenter', fn () => $this->revenueCenter?->property),
            'contract' => null,
            'attachments' => $this->attachment_path === null ? [] : [
                [
                    'path' => $this->attachment_path,
                ],
            ],
            'revenue_center' => $this->whenLoaded('revenueCenter'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
