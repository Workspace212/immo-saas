<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Collaboration;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\Mandate;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('now', '+1 month');

        return [
            'agency_id' => Agency::factory(),
            'property_id' => fake()->boolean(70) ? Property::factory() : null,
            'client_id' => fake()->boolean(60) ? Client::factory() : null,
            'owner_id' => fake()->boolean(50) ? Owner::factory() : null,
            'provider_id' => fake()->boolean(30) ? Provider::factory() : null,
            'contract_id' => fake()->boolean(30) ? Contract::factory() : null,
            'mandate_id' => fake()->boolean(30) ? Mandate::factory() : null,
            'complaint_id' => fake()->boolean(30) ? Complaint::factory() : null,
            'collaboration_id' => fake()->boolean(30) ? Collaboration::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'appointment_number' => fake()->unique()->bothify('APT-####??'),
            'appointment_type' => fake()->randomElement([
                'visit',
                'signature',
                'meeting',
                'call',
                'inspection',
                'key_handover',
                'client_follow_up',
                'owner_follow_up',
                'complaint_intervention',
                'other',
            ]),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'start_at' => $startAt,
            'end_at' => fake()->dateTimeBetween($startAt, '+2 hours'),
            'location' => fake()->optional()->address(),
            'status' => fake()->randomElement(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show']),
            'priority' => fake()->randomElement(['low', 'normal', 'urgent']),
            'external_calendar_provider' => fake()->optional()->randomElement(['google', 'outlook', 'apple']),
            'external_calendar_id' => fake()->optional()->uuid(),
            'external_event_id' => fake()->optional()->uuid(),
            'sync_status' => fake()->optional()->randomElement(['pending', 'synced', 'failed']),
            'last_synced_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
