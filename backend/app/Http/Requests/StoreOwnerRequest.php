<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerRequest extends FormRequest
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
            'agency_id' => ['required', 'integer', 'exists:agencies,id'],
            'owner_type' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'cin' => ['nullable', 'string', 'max:255'],
            'passport' => ['nullable', 'string', 'max:255'],
            'cin_passport' => ['nullable', 'string', 'max:255'],
            'ice' => ['nullable', 'string', 'max:255'],
            'rc' => ['nullable', 'string', 'max:255'],
            'if_number' => ['nullable', 'string', 'max:255'],
            'patente' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'max:10'],
            'representative_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'rib_iban' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function mergeNormalizedFields(): void
    {
        $data = [];

        if ($this->filled('owner_type')) {
            $data['type'] = $this->input('owner_type');
        }

        if (! $this->filled('full_name')) {
            $fullName = trim((string) ($this->input('first_name', '') . ' ' . $this->input('last_name', '')));

            if ($fullName !== '') {
                $data['full_name'] = $fullName;
            }
        }

        if (! $this->filled('cin_passport')) {
            $data['cin_passport'] = $this->input('cin') ?: $this->input('passport');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
