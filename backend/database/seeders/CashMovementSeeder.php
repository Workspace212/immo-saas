<?php

namespace Database\Seeders;

use App\Models\CashMovement;
use Illuminate\Database\Seeder;

class CashMovementSeeder extends Seeder
{
    public function run(): void
    {
        CashMovement::factory()->count(10)->create();
    }
}
