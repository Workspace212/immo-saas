<?php

namespace Database\Seeders;

use App\Models\FinancialTransaction;
use Illuminate\Database\Seeder;

class FinancialTransactionSeeder extends Seeder
{
    public function run(): void
    {
        FinancialTransaction::factory()->count(10)->create();
    }
}
