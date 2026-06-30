<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\PropertyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PropertyStatus> */
class PropertyStatusFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Brouillon', 'Disponible', 'Réservé', 'Vendu', 'Loué']);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => fake()->hexColor(),
            'display_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}
