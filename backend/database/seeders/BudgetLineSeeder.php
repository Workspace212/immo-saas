<?php

namespace Database\Seeders;

use App\Models\BudgetLine;
use Illuminate\Database\Seeder;

class BudgetLineSeeder extends Seeder
{
    public function run(): void
    {
        BudgetLine::factory()->count(10)->create();
    }
}
