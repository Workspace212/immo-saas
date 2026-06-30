<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CustomField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<CustomField> */
class CustomFieldFactory extends Factory
{
    public function definition(): array
    {
        $label = fake()->words(2, true);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'module' => fake()->randomElement(['properties', 'contracts', 'owners', 'clients']),
            'field_key' => Str::slug($label, '_').'_'.fake()->unique()->numberBetween(100, 999),
            'label' => Str::title($label),
            'field_type' => fake()->randomElement(['text', 'number', 'date', 'select', 'checkbox']),
            'is_required' => fake()->boolean(30),
            'is_visible' => true,
            'display_order' => fake()->numberBetween(0, 20),
            'options' => ['choices' => ['Option A', 'Option B']],
        ];
    }
}
