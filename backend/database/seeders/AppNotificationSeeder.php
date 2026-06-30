<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use Illuminate\Database\Seeder;

class AppNotificationSeeder extends Seeder
{
    public function run(): void
    {
        AppNotification::factory()->count(10)->create();
    }
}
