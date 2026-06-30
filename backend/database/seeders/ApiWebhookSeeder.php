<?php

namespace Database\Seeders;

use App\Models\ApiWebhook;
use Illuminate\Database\Seeder;

class ApiWebhookSeeder extends Seeder
{
    public function run(): void
    {
        ApiWebhook::factory()->count(10)->create();
    }
}
