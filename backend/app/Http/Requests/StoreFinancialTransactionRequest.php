<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['required', 'integer', 'exists:agencies,id'],
            'source_account_id' => ['nullable', 'integer', 'exists:financial_accounts,id'],
            'destination_account_id' => ['nullable', 'integer', 'exists:financial_accounts,id'],
            'financial_category_id' => ['nullable', 'integer', 'exists:financial_categories,id'],
            'revenue_center_id' => ['nullable', 'integer', 'exists:revenue_centers,id'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'validated_by' => ['nullable', 'integer', 'exists:users,id'],
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
            'category.id' => ['nullable', 'integer', 'exists:financial_categories,id'],
            'account' => ['nullable', 'array'],
            'account.source_id' => ['nullable', 'integer', 'exists:financial_accounts,id'],
            'account.destination_id' => ['nullable', 'integer', 'exists:financial_accounts,id'],
        ];
    }
}
