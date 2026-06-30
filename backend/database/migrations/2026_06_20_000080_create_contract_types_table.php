<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('category')->index();
            $table->boolean('is_exclusive')->default(false)->index();
            $table->boolean('is_custom')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('display_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_types');
    }
};
