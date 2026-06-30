<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentParticipant;
use App\Models\Client;
use App\Models\Owner;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AppointmentParticipant> */
class AppointmentParticipantFactory extends Factory
{
    public function definition(): array
    {
        $participantType = fake()->randomElement(['user', 'client', 'owner', 'provider', 'external']);

        return [
            'appointment_id' => Appointment::factory(),
            'user_id' => $participantType === 'user' ? User::factory() : null,
            'client_id' => $participantType === 'client' ? Client::factory() : null,
            'owner_id' => $participantType === 'owner' ? Owner::factory() : null,
            'provider_id' => $participantType === 'provider' ? Provider::factory() : null,
            'participant_type' => $participantType,
            'name' => $participantType === 'external' ? fake()->name() : null,
            'email' => $participantType === 'external' ? fake()->safeEmail() : null,
            'phone' => $participantType === 'external' ? fake()->phoneNumber() : null,
            'status' => fake()->randomElement(['pending', 'accepted', 'declined', 'tentative', 'attended', 'absent']),
            'responded_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
