<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UsesTenantValidationRules;
use App\Models\FinancialTransaction;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialTransactionRequest extends FormRequest
{
    use UsesTenantValidationRules;

    public function authorize(): bool
    {
        $financialTransaction = $this->route('financialTransaction');

        return $financialTransaction instanceof FinancialTransaction
            && ($this->user()?->can('update', $financialTransaction) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['prohibited'],
            'source_account_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('financial_accounts')],
            'destination_account_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('financial_accounts')],
            'financial_category_id' => ['sometimes', 'nullable', 'integer', $this->globalOrTenantExists('financial_categories')],
            'revenue_center_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('revenue_centers')],
            'created_by' => ['sometimes', 'nullable', 'integer', $this->sameAgencyUserExists()],
            'validated_by' => ['sometimes', 'nullable', 'integer', $this->sameAgencyUserExists()],
            'transaction_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'transaction_type' => ['sometimes', 'string', 'max:255'],
            'transaction_date' => ['sometimes', 'nullable', 'date'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'attachment_path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'validated_at' => ['sometimes', 'nullable', 'date'],
            'is_reconciled' => ['sometimes', 'nullable', 'boolean'],
            'reconciled_at' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'transaction' => ['sometimes', 'nullable', 'array'],
            'transaction.type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'transaction.date' => ['sometimes', 'nullable', 'date'],
            'transaction.amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'transaction.reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'attachments' => ['sometimes', 'nullable', 'array'],
            'attachments.*.path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'attachments.*.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'attachments.*.mime_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tax' => ['sometimes', 'nullable', 'array'],
            'tax.rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'tax.amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'category' => ['sometimes', 'nullable', 'array'],
            'category.id' => ['sometimes', 'nullable', 'integer', $this->globalOrTenantExists('financial_categories')],
            'account' => ['sometimes', 'nullable', 'array'],
            'account.source_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('financial_accounts')],
            'account.destination_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('financial_accounts')],
        ];
    }
}
