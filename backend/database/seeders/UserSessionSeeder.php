<?php

namespace Database\Seeders;

use App\Models\UserSession;
use Illuminate\Database\Seeder;

class UserSessionSeeder extends Seeder
{
    public function run(): void
    {
        UserSession::factory()->count(10)->create();
    }
}
