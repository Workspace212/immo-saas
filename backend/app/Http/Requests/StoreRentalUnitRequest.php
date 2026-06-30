<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalUnitRequest extends FormRequest
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
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'contract_id' => ['nullable', 'integer', 'exists:contracts,id'],
            'previous_rental_unit_id' => ['nullable', 'integer', 'exists:rental_units,id'],
            'assigned_agent_id' => ['nullable', 'integer', 'exists:users,id'],
            'rental_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'rent_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_status' => ['nullable', 'string', 'max:255'],
            'is_renewal' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'parties' => ['nullable', 'array'],
            'parties.*.client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'parties.*.party_type' => ['required_with:parties', 'string', 'max:255'],
            'parties.*.role' => ['nullable', 'string', 'max:255'],
            'parties.*.display_order' => ['nullable', 'integer', 'min:0'],
            'parties.*.signed' => ['nullable', 'boolean'],
            'parties.*.signature_date' => ['nullable', 'date'],
            'parties.*.notes' => ['nullable', 'string'],
            'payment_schedules' => ['nullable', 'array'],
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
            'documents' => ['nullable', 'array'],
            'documents.*.document_type' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.file_path' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.original_name' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
        ];
    }
}
