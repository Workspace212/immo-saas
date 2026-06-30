<?php

namespace Database\Seeders;

use App\Models\NotificationQueue;
use Illuminate\Database\Seeder;

class NotificationQueueSeeder extends Seeder
{
    public function run(): void
    {
        NotificationQueue::factory()->count(10)->create();
    }
}
