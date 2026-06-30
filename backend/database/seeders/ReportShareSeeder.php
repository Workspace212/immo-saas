<?php

namespace Database\Seeders;

use App\Models\ReportShare;
use Illuminate\Database\Seeder;

class ReportShareSeeder extends Seeder
{
    public function run(): void
    {
        ReportShare::factory()->count(10)->create();
    }
}
