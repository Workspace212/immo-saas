<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
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
            'property_type_id' => ['nullable', 'integer', 'exists:property_types,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'living_area_m2' => ['nullable', 'numeric', 'min:0'],
            'land_area_m2' => ['nullable', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'floors' => ['nullable', 'integer', 'min:0'],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:' . ((int) date('Y') + 1)],
            'status' => ['nullable', 'string', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
            'confidentiality_level' => ['nullable', 'string', 'max:255'],
            'owners' => ['nullable', 'array'],
            'owners.*.owner_id' => ['required_with:owners', 'integer', 'exists:owners,id'],
            'owners.*.ownership_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'owners.*.is_primary_owner' => ['nullable', 'boolean'],
            'owners.*.notes' => ['nullable', 'string'],
            'activities' => ['nullable', 'array'],
            'activities.*.activity_type' => ['required_with:activities', 'string', 'max:255'],
            'activities.*.price' => ['nullable', 'numeric', 'min:0'],
            'activities.*.currency' => ['nullable', 'string', 'size:3'],
            'activities.*.is_active' => ['nullable', 'boolean'],
            'activities.*.notes' => ['nullable', 'string'],
            'media' => ['nullable', 'array'],
            'media.*.media_type' => ['required_with:media', 'string', 'max:255'],
            'media.*.file_path' => ['required_with:media', 'string', 'max:255'],
            'media.*.title' => ['nullable', 'string', 'max:255'],
            'media.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'media.*.is_cover' => ['nullable', 'boolean'],
            'documents' => ['nullable', 'array'],
            'documents.*.document_type' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.file_path' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.original_name' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.notes' => ['nullable', 'string'],
            'availabilities' => ['nullable', 'array'],
            'availabilities.*.rental_unit_id' => ['nullable', 'integer', 'exists:rental_units,id'],
            'availabilities.*.availability_type' => ['required_with:availabilities', 'string', 'max:255'],
            'availabilities.*.start_at' => ['required_with:availabilities', 'date'],
            'availabilities.*.end_at' => ['required_with:availabilities', 'date', 'after_or_equal:availabilities.*.start_at'],
            'availabilities.*.title' => ['nullable', 'string', 'max:255'],
            'availabilities.*.notes' => ['nullable', 'string'],
            'availabilities.*.status' => ['nullable', 'string', 'max:255'],
        ];
    }
}
