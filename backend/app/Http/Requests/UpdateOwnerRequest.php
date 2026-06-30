<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeNormalizedFields();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['sometimes', 'integer', 'exists:agencies,id'],
            'owner_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'full_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'cin' => ['sometimes', 'nullable', 'string', 'max:255'],
            'passport' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cin_passport' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ice' => ['sometimes', 'nullable', 'string', 'max:255'],
            'rc' => ['sometimes', 'nullable', 'string', 'max:255'],
            'if_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'patente' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'preferred_language' => ['sometimes', 'nullable', 'string', 'max:10'],
            'representative_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'rib_iban' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    private function mergeNormalizedFields(): void
    {
        $data = [];

        if ($this->has('owner_type')) {
            $data['type'] = $this->input('owner_type');
        }

        if (! $this->filled('full_name') && ($this->filled('first_name') || $this->filled('last_name'))) {
            $fullName = trim((string) ($this->input('first_name', '') . ' ' . $this->input('last_name', '')));
            $data['full_name'] = $fullName !== '' ? $fullName : null;
        }

        if (! $this->filled('cin_passport') && ($this->filled('cin') || $this->filled('passport'))) {
            $data['cin_passport'] = $this->input('cin') ?: $this->input('passport');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
