<?php

namespace Database\Seeders;

use App\Models\FinancialClosing;
use Illuminate\Database\Seeder;

class FinancialClosingSeeder extends Seeder
{
    public function run(): void
    {
        FinancialClosing::factory()->count(10)->create();
    }
}
