<?php

namespace Database\Seeders;

use App\Models\BankReconciliation;
use Illuminate\Database\Seeder;

class BankReconciliationSeeder extends Seeder
{
    public function run(): void
    {
        BankReconciliation::factory()->count(10)->create();
    }
}
