<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
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
            'client_id' => ['sometimes', 'nullable', 'integer', 'exists:clients,id'],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'complaint_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'complaint_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'priority' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'provider_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'provider_phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'intervention_date' => ['sometimes', 'nullable', 'date'],
            'amount_paid' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'paid_by' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'documents' => ['sometimes', 'nullable', 'array'],
            'documents.*.document_type' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.file_path' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.original_name' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
            'providers' => ['sometimes', 'nullable', 'array'],
            'providers.*.provider_id' => ['required_with:providers', 'integer', 'exists:providers,id'],
            'providers.*.assigned_at' => ['nullable', 'date'],
            'providers.*.intervention_date' => ['nullable', 'date'],
            'providers.*.status' => ['nullable', 'string', 'max:255'],
            'providers.*.amount' => ['nullable', 'numeric', 'min:0'],
            'providers.*.notes' => ['nullable', 'string'],
        ];
    }
}
