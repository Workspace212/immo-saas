<?php

namespace Database\Seeders;

use App\Models\SystemEvent;
use Illuminate\Database\Seeder;

class SystemEventSeeder extends Seeder
{
    public function run(): void
    {
        SystemEvent::factory()->count(10)->create();
    }
}
