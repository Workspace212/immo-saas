<?php

namespace Database\Seeders;

use App\Models\DashboardSnapshot;
use Illuminate\Database\Seeder;

class DashboardSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        DashboardSnapshot::factory()->count(10)->create();
    }
}
