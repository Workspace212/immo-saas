<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->integer('sla_hours')->nullable()->index();
            $table->string('default_priority')->default('normal')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('display_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_types');
    }
};
