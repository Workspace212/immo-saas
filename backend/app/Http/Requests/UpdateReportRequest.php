<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
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
            'report_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'report_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters' => ['sometimes', 'nullable', 'array'],
            'columns' => ['sometimes', 'nullable', 'array'],
            'columns.*' => ['sometimes', 'nullable', 'string', 'max:255'],
            'grouping' => ['sometimes', 'nullable', 'array'],
            'sorting' => ['sometimes', 'nullable', 'array'],
            'format' => ['sometimes', 'nullable', 'string', 'max:50'],
            'schedule' => ['sometimes', 'nullable', 'array'],
            'schedule.frequency' => ['sometimes', 'nullable', 'string', 'max:255'],
            'schedule.send_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'schedule.recipients' => ['sometimes', 'nullable', 'array'],
            'sharing' => ['sometimes', 'nullable', 'array'],
            'sharing.users' => ['sometimes', 'nullable', 'array'],
            'period_start' => ['sometimes', 'nullable', 'date'],
            'period_end' => ['sometimes', 'nullable', 'date', 'after_or_equal:period_start'],
            'visibility' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_favorite' => ['sometimes', 'nullable', 'boolean'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
