<?php

namespace Database\Seeders;

use App\Models\ScheduledReport;
use Illuminate\Database\Seeder;

class ScheduledReportSeeder extends Seeder
{
    public function run(): void
    {
        ScheduledReport::factory()->count(10)->create();
    }
}
