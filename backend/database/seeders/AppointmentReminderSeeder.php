<?php

namespace Database\Seeders;

use App\Models\AppointmentReminder;
use Illuminate\Database\Seeder;

class AppointmentReminderSeeder extends Seeder
{
    public function run(): void
    {
        AppointmentReminder::factory()->count(10)->create();
    }
}
