<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'property_type_id' => fake()->boolean(80) ? PropertyType::factory() : null,
            'reference' => fake()->unique()->bothify('PROP-####??'),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'country' => 'Morocco',
            'city' => fake()->optional()->city(),
            'sector' => fake()->optional()->streetName(),
            'address' => fake()->optional()->address(),
            'latitude' => fake()->optional()->latitude(27, 36),
            'longitude' => fake()->optional()->longitude(-13, -1),
            'living_area_m2' => fake()->optional()->randomFloat(2, 40, 500),
            'land_area_m2' => fake()->optional()->randomFloat(2, 60, 2000),
            'bedrooms' => fake()->optional()->numberBetween(1, 8),
            'bathrooms' => fake()->optional()->numberBetween(1, 5),
            'floors' => fake()->optional()->numberBetween(1, 5),
            'year_built' => fake()->optional()->numberBetween(1970, now()->year),
            'status' => 'draft',
            'is_published' => false,
            'confidentiality_level' => 'internal',
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'updated_by' => fake()->boolean(50) ? User::factory() : null,
        ];
    }
}
