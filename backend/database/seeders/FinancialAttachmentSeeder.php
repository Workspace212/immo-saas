<?php

namespace Database\Seeders;

use App\Models\FinancialAttachment;
use Illuminate\Database\Seeder;

class FinancialAttachmentSeeder extends Seeder
{
    public function run(): void
    {
        FinancialAttachment::factory()->count(10)->create();
    }
}
