<?php

namespace Database\Seeders;

use App\Models\InvoicePayment;
use Illuminate\Database\Seeder;

class InvoicePaymentSeeder extends Seeder
{
    public function run(): void
    {
        InvoicePayment::factory()->count(10)->create();
    }
}
