<?php

namespace Database\Seeders;

use App\Models\CommissionPayment;
use Illuminate\Database\Seeder;

class CommissionPaymentSeeder extends Seeder
{
    public function run(): void
    {
        CommissionPayment::factory()->count(10)->create();
    }
}
