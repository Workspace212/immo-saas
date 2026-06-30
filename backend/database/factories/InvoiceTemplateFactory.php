<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<InvoiceTemplate> */
class InvoiceTemplateFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Modèle standard', 'Modèle moderne', 'Modèle minimal']);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'logo_path' => fake()->optional()->filePath(),
            'primary_color' => fake()->hexColor(),
            'secondary_color' => fake()->hexColor(),
            'footer_text' => fake()->optional()->sentence(),
            'template_data' => ['layout' => 'default', 'show_logo' => true],
            'is_default' => fake()->boolean(30),
            'is_active' => true,
        ];
    }
}
