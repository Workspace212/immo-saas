<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UsesTenantValidationRules;
use App\Models\FinancialTransaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTransactionRequest extends FormRequest
{
    use UsesTenantValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', FinancialTransaction::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['prohibited'],
            'source_account_id' => ['nullable', 'integer', $this->tenantExists('financial_accounts')],
            'destination_account_id' => ['nullable', 'integer', $this->tenantExists('financial_accounts')],
            'financial_category_id' => ['nullable', 'integer', $this->globalOrTenantExists('financial_categories')],
            'revenue_center_id' => ['nullable', 'integer', $this->tenantExists('revenue_centers')],
            'created_by' => ['nullable', 'integer', $this->sameAgencyUserExists()],
            'validated_by' => ['nullable', 'integer', $this->sameAgencyUserExists()],
            'transaction_number' => ['nullable', 'string', 'max:255'],
            'transaction_type' => ['required_without:transaction.type', 'string', 'max:255'],
            'transaction_date' => ['nullable', 'date'],
            'amount' => ['required_without:transaction.amount', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'validated_at' => ['nullable', 'date'],
            'is_reconciled' => ['nullable', 'boolean'],
            'reconciled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'transaction' => ['nullable', 'array'],
            'transaction.type' => ['required_without:transaction_type', 'string', 'max:255'],
            'transaction.date' => ['nullable', 'date'],
            'transaction.amount' => ['required_without:amount', 'numeric', 'min:0'],
            'transaction.reference' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.path' => ['nullable', 'string', 'max:255'],
            'attachments.*.name' => ['nullable', 'string', 'max:255'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:255'],
            'tax' => ['nullable', 'array'],
            'tax.rate' => ['nullable', 'numeric', 'min:0'],
            'tax.amount' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'array'],
            'category.id' => ['nullable', 'integer', $this->globalOrTenantExists('financial_categories')],
            'account' => ['nullable', 'array'],
            'account.source_id' => ['nullable', 'integer', $this->tenantExists('financial_accounts')],
            'account.destination_id' => ['nullable', 'integer', $this->tenantExists('financial_accounts')],
        ];
    }
}
