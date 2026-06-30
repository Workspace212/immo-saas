<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationTemplate> */
class NotificationTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(70) ? Agency::factory() : null,
            'template_key' => fake()->unique()->slug(3),
            'channel' => fake()->randomElement(['internal', 'email', 'sms', 'whatsapp', 'push']),
            'title_template' => fake()->optional()->sentence(4),
            'body_template' => 'Bonjour {{name}}, '.fake()->sentence(),
            'is_active' => true,
        ];
    }
}
