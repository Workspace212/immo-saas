<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollaborationRequest extends FormRequest
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
            'requesting_agent_id' => ['required', 'integer', 'exists:users,id'],
            'owner_agent_id' => ['required', 'integer', 'exists:users,id'],
            'collaboration_number' => ['nullable', 'string', 'max:255'],
            'collaboration_type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'request_message' => ['nullable', 'string'],
            'rejection_reason' => ['nullable', 'string'],
            'accepted_at' => ['nullable', 'date'],
            'rejected_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'cancelled_at' => ['nullable', 'date'],
            'commissions' => ['nullable', 'array'],
            'commissions.*.agent_id' => ['required_with:commissions', 'integer', 'exists:users,id'],
            'commissions.*.commission_role' => ['required_with:commissions', 'string', 'max:255'],
            'commissions.*.commission_type' => ['nullable', 'string', 'max:255'],
            'commissions.*.commission_value' => ['nullable', 'numeric', 'min:0'],
            'commissions.*.calculated_amount' => ['nullable', 'numeric', 'min:0'],
            'commissions.*.currency' => ['nullable', 'string', 'size:3'],
            'commissions.*.status' => ['nullable', 'string', 'max:255'],
            'commissions.*.validated_by' => ['nullable', 'integer', 'exists:users,id'],
            'commissions.*.validated_at' => ['nullable', 'date'],
            'commissions.*.notes' => ['nullable', 'string'],
        ];
    }
}
