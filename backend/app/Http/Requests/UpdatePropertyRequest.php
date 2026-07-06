<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UsesTenantValidationRules;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    use UsesTenantValidationRules;

    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            && ($this->user()?->can('update', $property) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agency_id' => ['prohibited'],
            'property_type_id' => ['sometimes', 'nullable', 'integer', $this->globalOrTenantExists('property_types')],
            'reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sector' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'living_area_m2' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'land_area_m2' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'bedrooms' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'bathrooms' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'floors' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'year_built' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:' . ((int) date('Y') + 1)],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'nullable', 'boolean'],
            'confidentiality_level' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owners' => ['sometimes', 'nullable', 'array'],
            'owners.*.owner_id' => ['required_with:owners', 'integer', $this->tenantExists('owners')],
            'owners.*.ownership_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'owners.*.is_primary_owner' => ['nullable', 'boolean'],
            'owners.*.notes' => ['nullable', 'string'],
            'activities' => ['sometimes', 'nullable', 'array'],
            'activities.*.activity_type' => ['required_with:activities', 'string', 'max:255'],
            'activities.*.price' => ['nullable', 'numeric', 'min:0'],
            'activities.*.currency' => ['nullable', 'string', 'size:3'],
            'activities.*.is_active' => ['nullable', 'boolean'],
            'activities.*.notes' => ['nullable', 'string'],
            'media' => ['sometimes', 'nullable', 'array'],
            'media.*.media_type' => ['required_with:media', 'string', 'max:255'],
            'media.*.file_path' => ['required_with:media', 'string', 'max:255'],
            'media.*.title' => ['nullable', 'string', 'max:255'],
            'media.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'media.*.is_cover' => ['nullable', 'boolean'],
            'documents' => ['sometimes', 'nullable', 'array'],
            'documents.*.document_type' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.file_path' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.original_name' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
            'availabilities' => ['sometimes', 'nullable', 'array'],
            'availabilities.*.rental_unit_id' => ['nullable', 'integer', $this->tenantExists('rental_units')],
            'availabilities.*.availability_type' => ['required_with:availabilities', 'string', 'max:255'],
            'availabilities.*.start_at' => ['required_with:availabilities', 'date'],
            'availabilities.*.end_at' => ['required_with:availabilities', 'date', 'after_or_equal:availabilities.*.start_at'],
            'availabilities.*.title' => ['nullable', 'string', 'max:255'],
            'availabilities.*.notes' => ['nullable', 'string'],
            'availabilities.*.status' => ['nullable', 'string', 'max:255'],
        ];
    }
}
