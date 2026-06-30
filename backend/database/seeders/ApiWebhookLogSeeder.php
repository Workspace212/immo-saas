<?php

namespace Database\Seeders;

use App\Models\ApiWebhookLog;
use Illuminate\Database\Seeder;

class ApiWebhookLogSeeder extends Seeder
{
    public function run(): void
    {
        ApiWebhookLog::factory()->count(10)->create();
    }
}
