<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
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
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required_without:message', 'string'],
            'message' => ['required_without:body', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:255'],
            'channel' => ['nullable', 'string', 'max:255'],
            'recipient' => ['required', 'string', 'max:255'],
            'template' => ['nullable', 'array'],
            'template.id' => ['nullable', 'integer', 'exists:notification_templates,id'],
            'notification_template_id' => ['nullable', 'integer', 'exists:notification_templates,id'],
            'payload' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'related_type' => ['nullable', 'string', 'max:255'],
            'related_id' => ['nullable', 'integer'],
        ];
    }
}
