<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyOwner>
 */
class PropertyOwnerFactory extends Factory
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
            'owner_id' => Owner::factory(),
            'ownership_percentage' => 100,
            'is_primary_owner' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
