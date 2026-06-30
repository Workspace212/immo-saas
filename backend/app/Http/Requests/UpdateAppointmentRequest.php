<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
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
            'owner_id' => ['sometimes', 'nullable', 'integer', 'exists:owners,id'],
            'provider_id' => ['sometimes', 'nullable', 'integer', 'exists:providers,id'],
            'contract_id' => ['sometimes', 'nullable', 'integer', 'exists:contracts,id'],
            'mandate_id' => ['sometimes', 'nullable', 'integer', 'exists:mandates,id'],
            'complaint_id' => ['sometimes', 'nullable', 'integer', 'exists:complaints,id'],
            'collaboration_id' => ['sometimes', 'nullable', 'integer', 'exists:collaborations,id'],
            'appointment_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'appointment_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'end_at' => ['sometimes', 'nullable', 'date'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'priority' => ['sometimes', 'nullable', 'string', 'max:255'],
            'external_calendar_provider' => ['sometimes', 'nullable', 'string', 'max:255'],
            'external_calendar_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'external_event_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sync_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_synced_at' => ['sometimes', 'nullable', 'date'],
            'participants' => ['sometimes', 'nullable', 'array'],
            'participants.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'participants.*.client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'participants.*.owner_id' => ['nullable', 'integer', 'exists:owners,id'],
            'participants.*.provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'participants.*.participant_type' => ['required_with:participants', 'string', 'max:255'],
            'participants.*.name' => ['nullable', 'string', 'max:255'],
            'participants.*.email' => ['nullable', 'email', 'max:255'],
            'participants.*.phone' => ['nullable', 'string', 'max:255'],
            'participants.*.status' => ['nullable', 'string', 'max:255'],
            'participants.*.notes' => ['nullable', 'string'],
            'reminders' => ['sometimes', 'nullable', 'array'],
            'reminders.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'reminders.*.reminder_type' => ['required_with:reminders', 'string', 'max:255'],
            'reminders.*.remind_at' => ['required_with:reminders', 'date'],
            'reminders.*.status' => ['nullable', 'string', 'max:255'],
            'reminders.*.sent_at' => ['nullable', 'date'],
            'reminders.*.channel' => ['nullable', 'string', 'max:255'],
            'reminders.*.error_message' => ['nullable', 'string'],
        ];
    }
}
