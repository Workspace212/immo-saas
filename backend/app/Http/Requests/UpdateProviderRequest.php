<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderRequest extends FormRequest
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
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'provider_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ice' => ['sometimes', 'nullable', 'string', 'max:255'],
            'rc' => ['sometimes', 'nullable', 'string', 'max:255'],
            'if_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'patente' => ['sometimes', 'nullable', 'string', 'max:255'],
            'rating' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:5'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
