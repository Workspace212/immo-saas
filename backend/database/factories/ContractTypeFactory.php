<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\ContractType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ContractType> */
class ContractTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Location simple', 'Vente', 'Gestion locative', 'Mandat exclusif']);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['rental', 'sale', 'property_management']),
            'is_exclusive' => str_contains(Str::lower($name), 'exclusif'),
            'is_custom' => fake()->boolean(30),
            'is_active' => true,
            'display_order' => fake()->numberBetween(0, 20),
        ];
    }
}
