<?php

namespace Database\Factories;

use App\Models\PropertyInspectionPhoto;
use App\Models\PropertyInspectionRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyInspectionPhoto>
 */
class PropertyInspectionPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalName = fake()->word().'.jpg';

        return [
            'property_inspection_room_id' => PropertyInspectionRoom::factory(),
            'uploaded_by' => fake()->boolean(80) ? User::factory() : null,
            'file_path' => 'inspection-photos/'.$originalName,
            'original_name' => $originalName,
            'title' => fake()->optional()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'taken_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'display_order' => fake()->numberBetween(0, 20),
            'is_cover' => false,
            'latitude' => fake()->optional()->latitude(27, 36),
            'longitude' => fake()->optional()->longitude(-13, -1),
        ];
    }
}
