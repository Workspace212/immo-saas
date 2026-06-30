<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDashboardSnapshotRequest extends FormRequest
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
            'snapshot_name' => ['sometimes', 'string', 'max:255'],
            'snapshot_date' => ['sometimes', 'date'],
            'period' => ['sometimes', 'string', 'max:255'],
            'filters' => ['sometimes', 'nullable', 'array'],
            'widgets' => ['sometimes', 'nullable', 'array'],
            'metrics' => ['sometimes', 'nullable', 'array'],
            'comparison' => ['sometimes', 'nullable', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
