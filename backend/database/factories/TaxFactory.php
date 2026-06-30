<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Tax> */
class TaxFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['TVA', 'Taxe de services', 'Taxe communale']);

        return [
            'agency_id' => fake()->boolean(70) ? Agency::factory() : null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'tax_type' => fake()->randomElement(['vat', 'withholding', 'local', 'custom']),
            'is_active' => true,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
