<?php

namespace Database\Seeders;

use App\Models\AppointmentParticipant;
use Illuminate\Database\Seeder;

class AppointmentParticipantSeeder extends Seeder
{
    public function run(): void
    {
        AppointmentParticipant::factory()->count(10)->create();
    }
}
