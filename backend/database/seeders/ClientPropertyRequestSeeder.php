<?php

namespace Database\Seeders;

use App\Models\ClientPropertyRequest;
use Illuminate\Database\Seeder;

class ClientPropertyRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClientPropertyRequest::factory()->count(10)->create();
    }
}
