<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
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
            'report_name' => ['required', 'string', 'max:255'],
            'report_type' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'filters' => ['nullable', 'array'],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['nullable', 'string', 'max:255'],
            'grouping' => ['nullable', 'array'],
            'sorting' => ['nullable', 'array'],
            'format' => ['nullable', 'string', 'max:50'],
            'schedule' => ['nullable', 'array'],
            'schedule.frequency' => ['nullable', 'string', 'max:255'],
            'schedule.send_time' => ['nullable', 'date_format:H:i'],
            'schedule.recipients' => ['nullable', 'array'],
            'sharing' => ['nullable', 'array'],
            'sharing.users' => ['nullable', 'array'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'visibility' => ['nullable', 'string', 'max:255'],
            'is_favorite' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
