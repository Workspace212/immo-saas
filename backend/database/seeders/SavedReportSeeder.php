<?php

namespace Database\Seeders;

use App\Models\SavedReport;
use Illuminate\Database\Seeder;

class SavedReportSeeder extends Seeder
{
    public function run(): void
    {
        SavedReport::factory()->count(10)->create();
    }
}
