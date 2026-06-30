<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'owner_id' => ['nullable', 'integer', 'exists:owners,id'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'contract_id' => ['nullable', 'integer', 'exists:contracts,id'],
            'mandate_id' => ['nullable', 'integer', 'exists:mandates,id'],
            'complaint_id' => ['nullable', 'integer', 'exists:complaints,id'],
            'collaboration_id' => ['nullable', 'integer', 'exists:collaborations,id'],
            'appointment_number' => ['nullable', 'string', 'max:255'],
            'appointment_type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:255'],
            'external_calendar_provider' => ['nullable', 'string', 'max:255'],
            'external_calendar_id' => ['nullable', 'string', 'max:255'],
            'external_event_id' => ['nullable', 'string', 'max:255'],
            'sync_status' => ['nullable', 'string', 'max:255'],
            'last_synced_at' => ['nullable', 'date'],
            'participants' => ['nullable', 'array'],
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
            'reminders' => ['nullable', 'array'],
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
