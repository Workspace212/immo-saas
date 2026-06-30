<?php

namespace Database\Seeders;

use App\Models\ReportExport;
use Illuminate\Database\Seeder;

class ReportExportSeeder extends Seeder
{
    public function run(): void
    {
        ReportExport::factory()->count(10)->create();
    }
}
