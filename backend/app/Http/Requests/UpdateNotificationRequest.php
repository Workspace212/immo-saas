<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationRequest extends FormRequest
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
            'agency_id' => ['sometimes', 'nullable', 'integer', 'exists:agencies,id'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string'],
            'message' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'priority' => ['sometimes', 'nullable', 'string', 'max:255'],
            'channel' => ['sometimes', 'nullable', 'string', 'max:255'],
            'recipient' => ['sometimes', 'nullable', 'string', 'max:255'],
            'template' => ['sometimes', 'nullable', 'array'],
            'template.id' => ['sometimes', 'nullable', 'integer', 'exists:notification_templates,id'],
            'notification_template_id' => ['sometimes', 'nullable', 'integer', 'exists:notification_templates,id'],
            'payload' => ['sometimes', 'nullable', 'array'],
            'data' => ['sometimes', 'nullable', 'array'],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'action_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'related_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'related_id' => ['sometimes', 'nullable', 'integer'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
