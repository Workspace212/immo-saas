<?php

namespace Database\Seeders;

use App\Models\NotificationPreference;
use Illuminate\Database\Seeder;

class NotificationPreferenceSeeder extends Seeder
{
    public function run(): void
    {
        NotificationPreference::factory()->count(10)->create();
    }
}
