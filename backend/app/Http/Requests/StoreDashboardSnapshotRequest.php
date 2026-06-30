<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDashboardSnapshotRequest extends FormRequest
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
            'snapshot_name' => ['required', 'string', 'max:255'],
            'snapshot_date' => ['required', 'date'],
            'period' => ['required', 'string', 'max:255'],
            'filters' => ['nullable', 'array'],
            'widgets' => ['nullable', 'array'],
            'metrics' => ['nullable', 'array'],
            'comparison' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
