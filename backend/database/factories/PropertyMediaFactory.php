<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyMedia>
 */
class PropertyMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mediaType = fake()->randomElement([
            'photo',
            'video',
            'floor_plan',
            'virtual_tour',
        ]);

        return [
            'property_id' => Property::factory(),
            'media_type' => $mediaType,
            'file_path' => 'properties/'.fake()->uuid().'.jpg',
            'title' => fake()->optional()->sentence(3),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_cover' => false,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
        ];
    }
}
