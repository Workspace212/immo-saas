<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PropertyType>
 */
class PropertyTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'agency_id' => fake()->boolean(70) ? Agency::factory() : null,
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
