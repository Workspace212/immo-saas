<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyActivity>
 */
class PropertyActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'activity_type' => fake()->randomElement([
                'sale',
                'long_term_rent',
                'short_term_rent',
                'property_management',
                'promotion',
            ]),
            'price' => fake()->optional()->randomFloat(2, 1000, 5000000),
            'currency' => 'MAD',
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
