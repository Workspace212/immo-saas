<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UsesTenantValidationRules;
use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    use UsesTenantValidationRules;

    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        return $appointment instanceof Appointment
            && ($this->user()?->can('update', $appointment) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['prohibited'],
            'property_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('properties')],
            'client_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('clients')],
            'owner_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('owners')],
            'provider_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('providers')],
            'contract_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('contracts')],
            'mandate_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('mandates')],
            'complaint_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('complaints')],
            'collaboration_id' => ['sometimes', 'nullable', 'integer', $this->tenantExists('collaborations')],
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
            'participants.*.user_id' => ['nullable', 'integer', $this->sameAgencyUserExists()],
            'participants.*.client_id' => ['nullable', 'integer', $this->tenantExists('clients')],
            'participants.*.owner_id' => ['nullable', 'integer', $this->tenantExists('owners')],
            'participants.*.provider_id' => ['nullable', 'integer', $this->tenantExists('providers')],
            'participants.*.participant_type' => ['required_with:participants', 'string', 'max:255'],
            'participants.*.name' => ['nullable', 'string', 'max:255'],
            'participants.*.email' => ['nullable', 'email', 'max:255'],
            'participants.*.phone' => ['nullable', 'string', 'max:255'],
            'participants.*.status' => ['nullable', 'string', 'max:255'],
            'participants.*.notes' => ['nullable', 'string'],
            'reminders' => ['sometimes', 'nullable', 'array'],
            'reminders.*.user_id' => ['nullable', 'integer', $this->sameAgencyUserExists()],
            'reminders.*.reminder_type' => ['required_with:reminders', 'string', 'max:255'],
            'reminders.*.remind_at' => ['required_with:reminders', 'date'],
            'reminders.*.status' => ['nullable', 'string', 'max:255'],
            'reminders.*.sent_at' => ['nullable', 'date'],
            'reminders.*.channel' => ['nullable', 'string', 'max:255'],
            'reminders.*.error_message' => ['nullable', 'string'],
        ];
    }
}
