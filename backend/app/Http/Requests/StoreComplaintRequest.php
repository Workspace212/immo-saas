<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
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
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'complaint_number' => ['nullable', 'string', 'max:255'],
            'complaint_type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'provider_name' => ['nullable', 'string', 'max:255'],
            'provider_phone' => ['nullable', 'string', 'max:255'],
            'intervention_date' => ['nullable', 'date'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'paid_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*.document_type' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.file_path' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.original_name' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
            'providers' => ['nullable', 'array'],
            'providers.*.provider_id' => ['required_with:providers', 'integer', 'exists:providers,id'],
            'providers.*.assigned_at' => ['nullable', 'date'],
            'providers.*.intervention_date' => ['nullable', 'date'],
            'providers.*.status' => ['nullable', 'string', 'max:255'],
            'providers.*.amount' => ['nullable', 'numeric', 'min:0'],
            'providers.*.notes' => ['nullable', 'string'],
        ];
    }
}
