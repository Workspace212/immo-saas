<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'owner_id' => ['nullable', 'integer', 'exists:owners,id'],
            'contract_id' => ['nullable', 'integer', 'exists:contracts,id'],
            'financial_transaction_id' => ['nullable', 'integer', 'exists:financial_transactions,id'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'currency' => ['nullable', 'string', 'size:3'],
            'subtotal_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'remaining_amount' => ['nullable', 'numeric', 'min:0'],
            'pdf_path' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'lines' => ['nullable', 'array'],
            'lines.*.description' => ['required_with:lines', 'string'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'lines.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.line_total' => ['nullable', 'numeric', 'min:0'],
            'lines.*.display_order' => ['nullable', 'integer', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.financial_account_id' => ['nullable', 'integer', 'exists:financial_accounts,id'],
            'payments.*.received_by' => ['nullable', 'integer', 'exists:users,id'],
            'payments.*.payment_number' => ['nullable', 'string', 'max:255'],
            'payments.*.payment_date' => ['nullable', 'date'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
            'payments.*.currency' => ['nullable', 'string', 'size:3'],
            'payments.*.payment_method' => ['nullable', 'string', 'max:255'],
            'payments.*.reference' => ['nullable', 'string', 'max:255'],
            'payments.*.receipt_number' => ['nullable', 'string', 'max:255'],
            'payments.*.receipt_path' => ['nullable', 'string', 'max:255'],
            'payments.*.status' => ['nullable', 'string', 'max:255'],
            'payments.*.notes' => ['nullable', 'string'],
            'taxes' => ['nullable', 'array'],
            'discounts' => ['nullable', 'array'],
        ];
    }
}
