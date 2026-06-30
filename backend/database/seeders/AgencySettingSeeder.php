<?php

namespace Database\Seeders;

use App\Models\AgencySetting;
use Illuminate\Database\Seeder;

class AgencySettingSeeder extends Seeder
{
    public function run(): void
    {
        AgencySetting::factory()->count(10)->create();
    }
}
