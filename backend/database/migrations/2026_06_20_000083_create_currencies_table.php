<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('iso_code', 3)->index();
            $table->string('symbol')->nullable();
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'iso_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
