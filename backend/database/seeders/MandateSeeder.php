<?php

namespace Database\Seeders;

use App\Models\Mandate;
use Illuminate\Database\Seeder;

class MandateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mandate::factory()->count(10)->create();
    }
}
