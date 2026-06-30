<?php

namespace Database\Seeders;

use App\Models\OwnerDisbursement;
use Illuminate\Database\Seeder;

class OwnerDisbursementSeeder extends Seeder
{
    public function run(): void
    {
        OwnerDisbursement::factory()->count(10)->create();
    }
}
