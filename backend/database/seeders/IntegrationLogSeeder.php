<?php

namespace Database\Seeders;

use App\Models\IntegrationLog;
use Illuminate\Database\Seeder;

class IntegrationLogSeeder extends Seeder
{
    public function run(): void
    {
        IntegrationLog::factory()->count(10)->create();
    }
}
