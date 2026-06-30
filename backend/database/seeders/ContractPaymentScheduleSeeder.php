<?php

namespace Database\Seeders;

use App\Models\ContractPaymentSchedule;
use Illuminate\Database\Seeder;

class ContractPaymentScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContractPaymentSchedule::factory()->count(10)->create();
    }
}
