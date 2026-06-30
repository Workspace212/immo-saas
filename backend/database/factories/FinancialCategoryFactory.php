<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\FinancialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<FinancialCategory> */
class FinancialCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Revenus', 'Dépenses', 'Commissions', 'Loyers', 'Ventes', 'Taxes']);

        return [
            'agency_id' => fake()->boolean(70) ? Agency::factory() : null,
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'category_type' => fake()->randomElement(['income', 'expense', 'commission', 'rent', 'sale', 'tax', 'custom']),
            'is_default' => fake()->boolean(50),
            'is_active' => true,
            'display_order' => fake()->numberBetween(0, 20),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
