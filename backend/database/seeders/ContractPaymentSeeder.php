<?php

namespace Database\Seeders;

use App\Models\ContractPayment;
use Illuminate\Database\Seeder;

class ContractPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContractPayment::factory()->count(10)->create();
    }
}
