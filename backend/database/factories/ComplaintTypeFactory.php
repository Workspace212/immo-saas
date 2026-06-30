<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\ComplaintType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ComplaintType> */
class ComplaintTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Plomberie', 'Électricité', 'Bruit', 'Paiement', 'Entretien']);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'name' => $name,
            'slug' => Str::slug($name),
            'sla_hours' => fake()->randomElement([24, 48, 72]),
            'default_priority' => fake()->randomElement(['low', 'normal', 'urgent', 'critical']),
            'is_active' => true,
            'display_order' => fake()->numberBetween(0, 20),
        ];
    }
}
