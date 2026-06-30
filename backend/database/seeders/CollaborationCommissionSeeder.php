<?php

namespace Database\Seeders;

use App\Models\CollaborationCommission;
use Illuminate\Database\Seeder;

class CollaborationCommissionSeeder extends Seeder
{
    public function run(): void
    {
        CollaborationCommission::factory()->count(10)->create();
    }
}
