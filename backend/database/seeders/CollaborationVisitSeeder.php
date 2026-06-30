<?php

namespace Database\Seeders;

use App\Models\CollaborationVisit;
use Illuminate\Database\Seeder;

class CollaborationVisitSeeder extends Seeder
{
    public function run(): void
    {
        CollaborationVisit::factory()->count(10)->create();
    }
}
