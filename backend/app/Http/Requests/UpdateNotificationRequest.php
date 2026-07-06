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

class UpdateNotificationRequest extends FormRequest
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
        $notification = $this->route('notification');

        return $notification instanceof AppNotification
            && ($this->user()?->can('update', $notification) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['prohibited'],
            'user_id' => ['sometimes', 'nullable', 'integer', $this->sameAgencyUserExists()],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string'],
            'message' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'priority' => ['sometimes', 'nullable', 'string', 'max:255'],
            'channel' => ['sometimes', 'nullable', 'string', 'max:255'],
            'recipient' => ['sometimes', 'nullable', 'string', 'max:255'],
            'template' => ['sometimes', 'nullable', 'array'],
            'template.id' => ['sometimes', 'nullable', 'integer', $this->globalOrTenantExists('notification_templates')],
            'notification_template_id' => ['sometimes', 'nullable', 'integer', $this->globalOrTenantExists('notification_templates')],
            'payload' => ['sometimes', 'nullable', 'array'],
            'data' => ['sometimes', 'nullable', 'array'],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'action_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'related_type' => ['sometimes', 'nullable', 'required_with:related_id', 'string', 'max:255', Rule::in(array_keys(self::RELATED_TYPES))],
            'related_id' => ['sometimes', 'nullable', 'required_with:related_type', 'integer'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
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
