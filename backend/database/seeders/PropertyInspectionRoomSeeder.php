<?php

namespace Database\Seeders;

use App\Models\PropertyInspectionRoom;
use Illuminate\Database\Seeder;

class PropertyInspectionRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyInspectionRoom::factory()->count(10)->create();
    }
}
