<?php

namespace Database\Seeders;

use App\Models\DashboardFavoriteFilter;
use Illuminate\Database\Seeder;

class DashboardFavoriteFilterSeeder extends Seeder
{
    public function run(): void
    {
        DashboardFavoriteFilter::factory()->count(10)->create();
    }
}
