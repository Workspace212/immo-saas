<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UsesTenantValidationRules;
use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    use UsesTenantValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Appointment::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['prohibited'],
            'property_id' => ['nullable', 'integer', $this->tenantExists('properties')],
            'client_id' => ['nullable', 'integer', $this->tenantExists('clients')],
            'owner_id' => ['nullable', 'integer', $this->tenantExists('owners')],
            'provider_id' => ['nullable', 'integer', $this->tenantExists('providers')],
            'contract_id' => ['nullable', 'integer', $this->tenantExists('contracts')],
            'mandate_id' => ['nullable', 'integer', $this->tenantExists('mandates')],
            'complaint_id' => ['nullable', 'integer', $this->tenantExists('complaints')],
            'collaboration_id' => ['nullable', 'integer', $this->tenantExists('collaborations')],
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
            'reminders' => ['nullable', 'array'],
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
