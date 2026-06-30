<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyDocument>
 */
class PropertyDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalName = fake()->word().'.pdf';

        return [
            'property_id' => Property::factory(),
            'document_type' => fake()->randomElement([
                'title_deed',
                'mandate',
                'floor_plan',
                'authorization',
                'contract',
                'certificate',
                'diagnostic',
                'other',
            ]),
            'file_path' => 'property-documents/'.$originalName,
            'original_name' => $originalName,
            'uploaded_by' => fake()->boolean(80) ? User::factory() : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
