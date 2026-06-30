<?php

namespace Database\Seeders;

use App\Models\InvoiceLine;
use Illuminate\Database\Seeder;

class InvoiceLineSeeder extends Seeder
{
    public function run(): void
    {
        InvoiceLine::factory()->count(10)->create();
    }
}
