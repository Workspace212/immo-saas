<?php

namespace Database\Seeders;

use App\Models\RevenueCenter;
use Illuminate\Database\Seeder;

class RevenueCenterSeeder extends Seeder
{
    public function run(): void
    {
        RevenueCenter::factory()->count(10)->create();
    }
}
