<?php

namespace Database\Seeders;

use App\Models\DashboardLayout;
use Illuminate\Database\Seeder;

class DashboardLayoutSeeder extends Seeder
{
    public function run(): void
    {
        DashboardLayout::factory()->count(10)->create();
    }
}
