<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
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
            'agency_id' => ['sometimes', 'integer', 'exists:agencies,id'],
            'property_id' => ['sometimes', 'nullable', 'integer', 'exists:properties,id'],
            'previous_contract_id' => ['sometimes', 'nullable', 'integer', 'exists:contracts,id'],
            'assigned_agent_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'contract_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contract_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'signed_at' => ['sometimes', 'nullable', 'date'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'deposit_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'charges_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'agency_fee_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'agency_fee_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'agency_fee_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'agency_fee_is_manual' => ['sometimes', 'nullable', 'boolean'],
            'owner_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'owner_amount_is_manual' => ['sometimes', 'nullable', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'parties' => ['sometimes', 'nullable', 'array'],
            'parties.*.owner_id' => ['nullable', 'integer', 'exists:owners,id'],
            'parties.*.client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'parties.*.party_type' => ['required_with:parties', 'string', 'max:255'],
            'parties.*.role' => ['nullable', 'string', 'max:255'],
            'parties.*.ownership_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'parties.*.display_order' => ['nullable', 'integer', 'min:0'],
            'parties.*.signed' => ['nullable', 'boolean'],
            'parties.*.signed_at' => ['nullable', 'date'],
            'parties.*.is_active' => ['nullable', 'boolean'],
            'parties.*.notes' => ['nullable', 'string'],
            'payment_schedules' => ['sometimes', 'nullable', 'array'],
            'payment_schedules.*.schedule_number' => ['nullable', 'string', 'max:255'],
            'payment_schedules.*.payment_type' => ['required_with:payment_schedules', 'string', 'max:255'],
            'payment_schedules.*.due_date' => ['required_with:payment_schedules', 'date'],
            'payment_schedules.*.amount_due' => ['required_with:payment_schedules', 'numeric', 'min:0'],
            'payment_schedules.*.currency' => ['nullable', 'string', 'size:3'],
            'payment_schedules.*.amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_schedules.*.remaining_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_schedules.*.status' => ['nullable', 'string', 'max:255'],
            'payment_schedules.*.paid_at' => ['nullable', 'date'],
            'payment_schedules.*.days_late' => ['nullable', 'integer', 'min:0'],
            'payment_schedules.*.penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_schedules.*.penalty_is_manual' => ['nullable', 'boolean'],
            'payment_schedules.*.owner_amount_due' => ['nullable', 'numeric', 'min:0'],
            'payment_schedules.*.owner_amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_schedules.*.owner_disbursement_status' => ['nullable', 'string', 'max:255'],
            'payment_schedules.*.receipt_number' => ['nullable', 'string', 'max:255'],
            'payment_schedules.*.receipt_generated_at' => ['nullable', 'date'],
            'payment_schedules.*.notes' => ['nullable', 'string'],
            'documents' => ['sometimes', 'nullable', 'array'],
            'documents.*.document_type' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.file_path' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.original_name' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
        ];
    }
}
