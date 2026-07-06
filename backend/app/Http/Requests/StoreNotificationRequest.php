<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UsesTenantValidationRules;
use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\Complaint;
use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreNotificationRequest extends FormRequest
{
    use UsesTenantValidationRules;

    private const RELATED_TYPES = [
        'appointment' => 'appointments',
        Appointment::class => 'appointments',
        'complaint' => 'complaints',
        Complaint::class => 'complaints',
        'contract' => 'contracts',
        Contract::class => 'contracts',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('create', AppNotification::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['prohibited'],
            'user_id' => ['nullable', 'integer', $this->sameAgencyUserExists()],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required_without:message', 'string'],
            'message' => ['required_without:body', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:255'],
            'channel' => ['nullable', 'string', 'max:255'],
            'recipient' => ['required', 'string', 'max:255'],
            'template' => ['nullable', 'array'],
            'template.id' => ['nullable', 'integer', $this->globalOrTenantExists('notification_templates')],
            'notification_template_id' => ['nullable', 'integer', $this->globalOrTenantExists('notification_templates')],
            'payload' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'related_type' => ['nullable', 'required_with:related_id', 'string', 'max:255', Rule::in(array_keys(self::RELATED_TYPES))],
            'related_id' => ['nullable', 'required_with:related_type', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateRelatedResource($validator);
        });
    }

    private function validateRelatedResource(Validator $validator): void
    {
        $relatedType = $this->input('related_type');
        $relatedId = $this->input('related_id');

        if ($relatedType === null || $relatedId === null || ! is_string($relatedType)) {
            return;
        }

        $table = self::RELATED_TYPES[$relatedType] ?? null;
        $agencyId = $this->tenantAgencyId();

        if ($table === null || $agencyId === null || ! DB::table($table)
            ->where('id', (int) $relatedId)
            ->where('agency_id', $agencyId)
            ->exists()) {
            $validator->errors()->add('related_id', 'The selected related resource is invalid.');
        }
    }
}
