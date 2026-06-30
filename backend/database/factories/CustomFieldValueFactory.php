<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CustomFieldValue> */
class CustomFieldValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'custom_field_id' => CustomField::factory(),
            'record_type' => fake()->randomElement(['Property', 'Contract', 'Owner', 'Client']),
            'record_id' => fake()->numberBetween(1, 1000),
            'value' => ['value' => fake()->word()],
        ];
    }
}
