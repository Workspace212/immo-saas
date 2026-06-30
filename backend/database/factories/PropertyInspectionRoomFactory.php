<?php

namespace Database\Factories;

use App\Models\PropertyInspection;
use App\Models\PropertyInspectionRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyInspectionRoom>
 */
class PropertyInspectionRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_inspection_id' => PropertyInspection::factory(),
            'room_name' => fake()->unique()->randomElement([
                'Salon',
                'Cuisine',
                'Chambre principale',
                'Chambre',
                'Salle de bain',
                'Terrasse',
            ]),
            'room_type' => fake()->optional()->randomElement([
                'living_room',
                'kitchen',
                'bedroom',
                'bathroom',
                'terrace',
                'other',
            ]),
            'floor' => fake()->optional()->randomElement(['RDC', '1', '2', '3']),
            'condition' => fake()->optional()->randomElement(['excellent', 'good', 'average', 'poor']),
            'cleanliness' => fake()->optional()->randomElement(['clean', 'acceptable', 'dirty']),
            'comments' => fake()->optional()->sentence(),
            'display_order' => fake()->numberBetween(0, 10),
        ];
    }
}
